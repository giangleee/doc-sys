<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceUser\UpdateServiceUserRequest;
use App\Http\Requests\ServiceUserRequest;
use App\Http\Requests\ServiceUser\ImportServiceUserRequest;
use App\Http\Requests\SettingMailDocumentRequest;
use App\Http\Resources\ListServiceUserCollection;
use App\Http\Resources\ListSearchServiceUserCollection;
use App\Http\Resources\ServiceUserResource;
use App\Repositories\FolderRepository;
use App\Repositories\MailDocumentRepository;
use App\Repositories\ServiceUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Validator\ImportServiceUser;


class ServiceUserController extends Controller
{
    protected $serviceUserRepository;
    protected $mailDocumentRepository;

    public function __construct(
        ServiceUserRepository $serviceUserRepository,
        MailDocumentRepository $mailDocumentRepository
    )
    {
        $this->serviceUserRepository = $serviceUserRepository;
        $this->mailDocumentRepository = $mailDocumentRepository;
    }

    public function index(Request $request)
    {
        return responseOK(new ListServiceUserCollection($this->serviceUserRepository->getList($request)));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $dataSave = $request->only(['name', 'code']);
//            $dataSave['office_id'] = auth()->user()->office_id;
            $dataSave['user_created'] = auth()->user()->id;
            $serviceUser = $this->serviceUserRepository->create($dataSave);
            DB::commit();
            return responseCreated(new ServiceUserResource($serviceUser));
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $serviceUser = $this->serviceUserRepository->findOrFail($id);
        return responseOK(new ServiceUserResource($serviceUser));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServiceUserRequest $request, $id)
    {
        $folder = new FolderRepository();
        DB::beginTransaction();
        try {
            $serviceUser = $this->serviceUserRepository->update($id, $request->only(['name']));
            $folder->updateNameFolderServiceUser($serviceUser->id, $serviceUser->code . 'ー' .$serviceUser->name);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $serviceUser = $this->serviceUserRepository->findOrFail($id);
            if (!$serviceUser->documents->isEmpty()) {
                return responseError(403, __('message.service_user.unauthorized_delete_service_user'));
            }
            $this->serviceUserRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->serviceUserRepository->deletes($request->ids);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError($exception->getCode(), $exception->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        DB::beginTransaction();
        try {
            if (empty($request->ids)) {
                return responseError(404, __('message.nothing_to_delete'));
            }
            $arrayIds = explode(',', $request->ids);
            $serviceUsers = $this->serviceUserRepository->getServiceUsersByIds($arrayIds);
            foreach ($serviceUsers as $serviceUser) {
                if ($serviceUser->documents->isNotEmpty()) {
                    return responseError(403, __('message.service_user.unauthorized_delete_service_user'));
                }
            }
            $this->serviceUserRepository->bulkDelete($arrayIds);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError($exception->getCode(), $exception->getMessage());
        }
    }

    public function setFileSetAccess($id, Request $request)
    {
        $serviceUser = $this->serviceUserRepository->findOrFail($id);
        $this->serviceUserRepository->setFileSetAccess($serviceUser, $request->all());
        return responseUpdatedOrDeleted();
    }

    public function settingAlertMail($id, SettingMailDocumentRequest $request)
    {
        DB::beginTransaction();
        try {
            $serviceUser = $this->serviceUserRepository->findOrFail($id);
            $serviceUserDocuments = $serviceUser->documents;
            if (!empty($serviceUserDocuments)) {
                foreach ($serviceUserDocuments as $document) {
                    $this->mailDocumentRepository->settingAlertMail($document, $request->all());
                }
            }
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function search(Request $request)
    {
        return responseOK(new ListSearchServiceUserCollection($this->serviceUserRepository->search($request->all())));
    }

    public function import(ImportServiceUserRequest $request)
    {
        //validate file import
        $validate = ImportServiceUser::validation($request);
        if (!empty($validate['errors'])) {
            return responseValidate($validate['errors'], $validate['message']);
        }

        $this->serviceUserRepository->import($request, $validate['data']);

        return responseOK();
    }
}
