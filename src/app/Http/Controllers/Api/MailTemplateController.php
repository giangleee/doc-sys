<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MailTemplateRequest;
use App\Http\Resources\MailTemplateCollection;
use App\Http\Resources\MailTemplateResource;
use App\Models\MailTemplate;
use App\Repositories\MailDocumentRepository;
use App\Repositories\MailTemplateRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailTemplateController extends Controller
{
    protected $mailTemplateRepository;
    protected $mailDocumentRepository;

    public function __construct(
        MailTemplateRepository $mailTemplateRepository,
        MailDocumentRepository $mailDocumentRepository
    )
    {
        $this->mailTemplateRepository = $mailTemplateRepository;
        $this->mailDocumentRepository = $mailDocumentRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $templates = $this->mailTemplateRepository->getList($request);
        return responseOK(new MailTemplateCollection($templates));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MailTemplateRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['code', 'name', 'subject', 'body']);
            $data['user_created'] = auth()->user()->id;
            $mailTemplate = $this->mailTemplateRepository->create($data);
            DB::commit();
            return responseCreated(new MailTemplateResource($mailTemplate));
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
        $mailTemplate = $this->mailTemplateRepository->findOrFail($id);
        return responseOK(new MailTemplateResource($mailTemplate));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(MailTemplateRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'subject', 'body']);
            $data['user_updated'] = auth()->user()->id;
            $this->mailTemplateRepository->update($id, $data);
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
            $mailTemplate = $this->mailTemplateRepository->getTemplateWithMailDocument($id);
            if ($mailTemplate->is_system == MailTemplate::IS_SYSTEM) {
                return responseError(403, __('message.unauthorized'));
            }
            if (!empty($mailTemplate->mailDocuments->toArray())) {
                return responseError(403, __('message.template.cant_delete'));
            }
            $this->mailTemplateRepository->delete($id);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
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
            $mailTemplates = $this->mailTemplateRepository->getMailTemplatesByIds($arrayIds);
            foreach ($mailTemplates as $mailTemplate) {
                if ($mailTemplate->is_system == MailTemplate::IS_SYSTEM) {
                    return responseError(403, __('message.unauthorized'));
                }
                if (!empty($mailTemplate->mailDocuments->toArray())) {
                    return responseError(403, __('message.template.cant_delete'));
                }
            }
            $this->mailTemplateRepository->bulkDelete($arrayIds);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError($exception->getCode(), $exception->getMessage());
        }
    }
}
