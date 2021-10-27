<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DocumentTypeCollection;
use App\Http\Resources\BasicDocumentTypeCollection;
use App\Http\Resources\DocumentTypeResource;
use App\Models\DocumentType;
// use App\Http\Requests\DocumentType
use App\Repositories\DocumentTypeRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\DB;

class DocumentTypeController extends Controller
{
    protected $documentTypeRepository;

    public function __construct(DocumentTypeRepository $documentTypeRepository)
    {
        $this->documentTypeRepository = $documentTypeRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->limit_data) {
            return responseOK(new BasicDocumentTypeCollection($this->documentTypeRepository->getList()));
        }

        return responseOK(new DocumentTypeCollection($this->documentTypeRepository->getList()));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'code', 'pattern_type', 'type', 'sort']);
            $data['user_created'] = auth()->user()->id;
            $documentType = $this->documentTypeRepository->create($data);
            DB::commit();
            return responseCreated(new DocumentTypeResource($documentType));
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
        $documentType = $this->documentTypeRepository->findOrFail($id);
        return responseOK(new DocumentTypeResource($documentType));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->only(['name', 'code', 'pattern_type', 'type', 'sort']);
            $data['user_updated'] = auth()->user()->id;
            $this->documentTypeRepository->update($id, $data);
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
    // public function destroy($id)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $documentType = $this->documentTypeRepository->getDocumentTypeWithID($id);
    //         if ($documentType->is_array == 1
    //         // DocumentType::IS_NOT_SYSTEM
    //         ) 
    //         {
    //             return responseError(403, __('message.unauthorized'));
    //         }
    //         if (empty($documentType->toArray())) {
    //             return responseError(403, __('message.template.cant_delete'));
    //         }
    //         $this->documentTypeRepository->delete($id);
    //         DB::commit();
    //         return responseUpdatedOrDeleted();
    //     } catch (\Exception $exception) {
    //         DB::rollBack();
    //         return responseError(500, $exception->getMessage());
    //     }
    // }


    /**
     * Delete the given organization
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->documentTypeRepository->delete($id);
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
            $documentType = $this->documentTypeRepository->getDocumentTypeWithIDS($arrayIds);
            foreach ($documentType as $documentType) {
                if ($documentType->is_system == DocumentType::IS_SYSTEM) {
                    return responseError(403, __('message.unauthorized'));
                }
                if (!empty($documentType->toArray())) {
                    return responseError(403, __('message.template.cant_delete'));
                }
            }
            $this->documentTypeRepository->bulkDelete($arrayIds);
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollback();
            return responseError($exception->getCode(), $exception->getMessage());
        }
    }
}
