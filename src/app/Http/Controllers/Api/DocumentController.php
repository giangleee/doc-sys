<?php

namespace App\Http\Controllers\Api;

use App\Helper\Constant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Requests\Document\UpdateDocumentRequest;
use App\Http\Requests\SettingMailDocumentRequest;
use App\Http\Resources\DocumentIncludeDeletedFileCollection;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\SearchDocumentCollection;
use App\Models\Document;
use App\Repositories\DocumentAttributeRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\DocumentTypeRepository;
use App\Repositories\FileHistoryRepository;
use App\Repositories\FileRepository;
use App\Repositories\MailDocumentRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TagRepository;
use App\Repositories\FileSetPermissionRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DocumentController extends Controller
{
    protected $documentRepository;
    protected $documentAttributeRepository;
    protected $fileRepository;
    protected $tagRepository;
    protected $roleRepository;
    protected $mailDocumentRepository;
    protected $fileSetPermissionRepository;
    protected $fileHistoryRepository;

    public function __construct(
        DocumentRepository $documentRepository,
        DocumentAttributeRepository $documentAttributeRepository,
        FileRepository $fileRepository,
        TagRepository $tagRepository,
        RoleRepository $roleRepository,
        MailDocumentRepository $mailDocumentRepository,
        FileSetPermissionRepository $fileSetPermissionRepository,
        FileHistoryRepository $fileHistoryRepository
    )
    {
        $this->documentRepository = $documentRepository;
        $this->documentAttributeRepository = $documentAttributeRepository;
        $this->tagRepository = $tagRepository;
        $this->fileRepository = $fileRepository;
        $this->roleRepository = $roleRepository;
        $this->mailDocumentRepository = $mailDocumentRepository;
        $this->fileSetPermissionRepository = $fileSetPermissionRepository;
        $this->fileHistoryRepository = $fileHistoryRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDocumentRequest $request)
    {
        DB::beginTransaction();
        try {
            // save info document
            $document = $this->documentRepository->saveDataDocument($request);
            if (isset($document['code']) && $document['code'] == Document::CODE_EXIT_DOCTYPE) {
                return responseOK($document);
            }
            // save document file
            $this->fileRepository->saveDocumentFiles($request->files_info, $document);

            // save document attribute
            if (isset($request->attribute_values) && !empty($request->attribute_values)) {
                $this->documentAttributeRepository->saveDocumentAttribute($request->attribute_values, $document);
            }
            // save document tag
            if (isset($request->tags) && !empty($request->tags)) {
                $this->tagRepository->saveDocumentTags($request->tags, $document);
            }

            // save mail document
            $this->mailDocumentRepository->saveMailDocument($document);
            DB::commit();
            return responseCreated(new DocumentResource($document));
        } catch (\Exception $exception) {
            DB::rollBack();
            if (strpos(get_class($exception), 'ValidationDateException') !== false) {
                return responseValidate($this->documentAttributeRepository->error, $exception->getMessage());
            }
            if (strpos(get_class($exception), 'AccessDeniedHttpException') !== false) {
                return responseError(403, $exception->getMessage());
            }
            if (strpos(get_class($exception), 'NotFoundHttpException') !== false) {
                return responseError(404, $exception->getMessage());
            }
            return responseError(500, $exception->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $document = $this->documentRepository->findOrFailDocument($id);
        return responseOK(new DocumentResource($document));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, UpdateDocumentRequest $request)
    {
        DB::beginTransaction();
        try {
            // update data document
            $document = $this->documentRepository->updateDataDocument($id, $request);

            // save file to S3
            $this->fileRepository->saveDocumentFiles($request->files_info, $document);

            // save document attribute
            if (isset($request->attribute_values)) {
                $this->documentAttributeRepository->saveDocumentAttribute($request->attribute_values, $document);
            }

            // save document tag
            if (isset($request->tags) && !empty($request->tags)) {
                $this->tagRepository->saveDocumentTags($request->tags, $document);
            }
            // set updated_at for document
            $document->updated_at = Carbon::now();
            $document->save();
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            if (strpos(get_class($exception), 'ValidationDateException') !== false) {
                return responseValidate($this->documentAttributeRepository->error, $exception->getMessage());
            }
            if (strpos(get_class($exception), 'AccessDeniedHttpException') !== false) {
                return responseError(403, $exception->getMessage());
            }
            if (strpos(get_class($exception), 'NotFoundHttpException') !== false) {
                return responseError(404, $exception->getMessage());
            }
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
            $document = $this->documentRepository->findOrFail($id);
            $serviceUserID = $document->service_user_id;
            if (auth()->user()->isStaff() && !$this->documentRepository->isOwner($document)) {
                return responseError(403, __('message.delete_document_failure'));
            }
            // delete all files belong to document
            $this->fileHistoryRepository->saveFileHistories($document->files);
            $document->files()->delete();

            //delete document
            $this->documentRepository->delete($id);
            //delete mail alert
            $this->mailDocumentRepository->deleteMailDocumentByDocumentID($id);
            //check document in filset
            if ($serviceUserID) {
                $documents = $this->documentRepository->getDocumentsInFileSet($serviceUserID);
                //todo refactor
                if ($documents->isEmpty()) {
                    $this->fileSetPermissionRepository->deleteByServiceUser($serviceUserID);
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
        $param = $request->all();
        $documents = $this->documentRepository->search($param);

        return responseOK(new SearchDocumentCollection($documents));
    }

    public function setDocumentAccess($id, Request $request)
    {
        DB::beginTransaction();
        try {
            $document = $this->documentRepository->findOrFail($id);
            $documentTypeRepository = new DocumentTypeRepository();
            $documentType = $documentTypeRepository->findOrFail($document->document_type_id);
            if (in_array($documentType->code, Constant::IS_B2C)) {
                return responseError(403, __('message.unauthorized'));
            }
            $this->documentRepository->setDocumentAccess($document, $request->all());
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function settingAlertMail($id, SettingMailDocumentRequest $request)
    {
        DB::beginTransaction();
        try {
            $document = $this->documentRepository->findOrFail($id);
            $this->mailDocumentRepository->settingAlertMail($document, $request->all());
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    public function createFileWithDoc(Request $request)
    {
        $document = $this->documentRepository->getDocWithDocTypeDocObj($request->all());
        try {
            if (empty($document)) {
                throw new NotFoundHttpException(__('message.can_not_find_document'));
            }
            $this->fileRepository->saveDocumentFiles($request->files_info, $document);
            return responseCreated(new DocumentResource($document));
        } catch (\Exception $exception) {
            return responseError(500, $exception->getMessage());
        }
    }

    public function searchDeletedFiles(Request $request)
    {
        $params = $request->all();
        $deletedFiles = $this->documentRepository->searchDeletedFiles($params);

        return responseOK(new DocumentIncludeDeletedFileCollection($deletedFiles));
    }
}
