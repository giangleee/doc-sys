<?php


namespace App\Repositories;

use App\Helper\Constant;
use App\Models\Document;
use App\Models\DocumentObject;
use App\Models\Role;
use App\Models\Division;
use App\Models\Office;
use App\Models\DocumentType;
use App\Models\MailDocument;
use App\Models\Attribute;
use App\Models\DocumentAttribute;
use App\Models\ServiceUser;
use App\Models\Store;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Carbon\Carbon;

class DocumentRepository extends BaseRepository
{
    public function getModel()
    {
        return Document::class;
    }

    public function getList()
    {
        return $this->model->get();
    }

    public function saveDataDocument(Request $request)
    {
        $dataDocument = $this->prepareDataToSave($request);
        // create new service user or get service user existed
        $serviceUserRepository = new ServiceUserRepository();
        $folderRepo = new FolderRepository();
        $storeRepo = new StoreRepository();

        $store = $storeRepo->findOrFail($request->store_id);
        $serviceUser = null;
        if (isset($dataDocument['service_user']) && !empty($dataDocument['service_user'])) {
            $dataDocument['service_user'] = json_decode($dataDocument['service_user'], 1);
            if (isset($dataDocument['service_user']['id']) && !empty($dataDocument['service_user']['id'])) {
                if (auth()->user()->isStaff()) {
                    $serviceUserIds = $serviceUserRepository->getServiceUserAvailableForStaff()->pluck('id')->toArray();
                    if (!in_array($dataDocument['service_user']['id'], $serviceUserIds)) {
                        throw new AccessDeniedHttpException(__('message.can_not_create_document'));
                    }
                }
                $serviceUser = $serviceUserRepository->find($dataDocument['service_user']['id']);
                if (!$serviceUser) {
                    throw new NotFoundHttpException(__('message.service_user_not_exist'));
                }

                $commonOfType = DocumentObject::COMMON_OBJECT[$request->document_type];
                if (in_array($request->document_object, $commonOfType)) {
                    $documentExit = Document::documentExit(
                        $dataDocument['document_object_id'],
                        $dataDocument['service_user']['id']
                    )->first();
                    if (!empty($documentExit)) {
                        return [
                            'code' => Document::CODE_EXIT_DOCTYPE,
                            'name_doc_object' => $documentExit->documentObject->name,
                            'name_service_user' => $documentExit->serviceUser->name
                        ];
                    }
                }
            }
            $dataDocument['service_user_id'] = $serviceUser->id;
            unset($dataDocument['service_user']);

            //Data create folder for service user
            $dataCreateFolder = [
                'service_user_id' => $serviceUser['id'],
                'service_user_name' => $serviceUser['name'],
                'service_user_code' => $serviceUser['code'],
                'store_id' => $request->store_id,
                'document_type_id' => $dataDocument['document_type_id'],
                'document_type' => $request->document_type,
                'store_name' => $store->name,
                'document_object' => $request->document_object
            ];
            if (empty($request->folder_id)) {
                $folderForServiceUser = $folderRepo->findOrCreateFolderForServiceUser($dataCreateFolder);

            } else {
                $params = $request->all();
                $params['service_user_id'] = $serviceUser['id'];
                $params['service_user_name'] = $serviceUser['name'];
                $params['service_user_code'] = $serviceUser['code'];
                $params['store_name'] = $store->name;
                $folderForServiceUser = $folderRepo->checkFolderSU($params);
            }
            $dataDocument['folder_id'] = $folderForServiceUser->id;
        }

        // create a document
        $document = $this->create($dataDocument);
        // get all position of staff
        $positionsId = $this->getAllPositionOfStaff();
        $dataPermission[$dataDocument['store_id']]['positions_id'] = implode(',', $positionsId);
        // attach management permission to the office
        if (!is_null($serviceUser)) {

            //Create file set management
            $saveFileSetManagement = $this->saveFileSetManagement($dataDocument);
            if (!empty($saveFileSetManagement)) {
                $fileSetPermissionRepository = new FileSetPermissionRepository();
                $fileSetPermission = $fileSetPermissionRepository->getFileSetPermission($saveFileSetManagement->id);
                if ($fileSetPermission->isNotEmpty()) {
                    foreach ($fileSetPermission as $permission) {
                        $dataPermission[$permission->store_id]['positions_id'] = $permission->positions_id;
                    }
                    $document->storePermission()->sync($dataPermission);
                } else {
                    $saveFileSetManagement->storePermission()->sync($dataPermission);
                    $document->storePermission()->sync($dataPermission);
                }
            }
        } else {
            $document->storePermission()->sync($dataPermission);
        }

        return $document;
    }

    public function updateDataDocument($id, Request $request)
    {
        $dataDocument['name'] = $request->name;
        $dataDocument['store_id'] = $request->store_id;
        $document = $this->findOrFail($id);

        if (isset($request->version) && !empty($request->version)) {
            if ($request->version != $document->version) {
                throw new AccessDeniedHttpException(__('message.update_document_failure'));
            } else {
                $dataDocument['version'] = $request->version + 1;
            }
        }
        $dataDocument['folder_id'] = $request->folder_id;
        if ($request->store_id != $document->store_id && !empty($document->serviceUser)) {
            //get data file set
            $filSet = $document->serviceUser->fileSetManagement(
                $document->store_id,
                $document->service_user_id,
                $document->document_type_id
            );
            //Set data file set update
            $dataFileSet = [
                'service_user_id' => $filSet->service_user_id,
                'document_type_id' => $filSet->document_type_id,
                'store_id' => $request->store_id
            ];


            //create file set when update doc
            $fileSetManagementRepo = new FileSetManagementRepository();
            $fileSetPermisisonRepo = new FileSetPermissionRepository();
            $createFileSet = $fileSetManagementRepo->firstOrCreateData($dataFileSet, $dataFileSet);

            //create file set permission when update doc
            foreach ($filSet->fileSetPermission as $fileSetPermission) {
                $dataFileSetPermission = [
                    'store_id' => $request->store_id,
                    'positions_id' => $fileSetPermission->positions_id,
                    'file_set_management_id' => $createFileSet->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
                $fileSetPermisisonRepo->firstOrCreateData(
                    [
                        'file_set_management_id' => $createFileSet->id,
                        'store_id' => $request->store_id,
                    ],
                    $dataFileSetPermission
                );
            }
        }
         //set data folder
        $serviceUser = json_decode($request->service_user, 1);
        if (count($serviceUser) > 0) {
            $dataCreateFolder = [
                'service_user_id' => $serviceUser['id'],
                'service_user_name' => $serviceUser['name'],
                'service_user_code' => $serviceUser['code'],
                'store_id' => $request->store_id,
                'document_type_id' => $document->document_type_id,
                'document_type' => $request->document_type,
                'document_object' => $request->document_object,
            ];

            //Create folder when update doc
            $folderRepo = new FolderRepository();
            $folderForServiceUser = $folderRepo->findOrCreateFolderForServiceUser($dataCreateFolder);
            $dataDocument['folder_id'] = $folderForServiceUser->id;
        }

        if (isset($request->document_object)) {
            $documentObjectRepository = new DocumentObjectRepository();
            $documentObject = $documentObjectRepository->findByCode($request->document_object);
            $dataDocument['document_object_id'] = $documentObject->id;
        }

        // attach role for document
        if ($document->store_id != $request->store_id) {
            $documentTypeRepository = new DocumentTypeRepository();
            $documentType = $documentTypeRepository->findOrFail($document->document_type_id);

            // get all position of staff
            $positionsId = $this->getAllPositionOfStaff();
            $dataPermission[$dataDocument['store_id']]['positions_id'] = implode(',', $positionsId);

            if (!in_array($documentType->code, Constant::IS_B2C)) {
                $document->storePermission()->detach($document->store_id);
                $document->storePermission()->attach($dataPermission);
            }
            // Update email when change Office in document.
            $storeRepo = new StoreRepository();
            $mailDocumentRepo = new MailDocumentRepository();
            $mailDocument = $mailDocumentRepo->getMailDocumentById($id);

            $documentMailTo = $mailDocument->to;

            $documentMailTo = explode(",", $documentMailTo);
            $currentDocumentEmail = $storeRepo->findOrFail($document->store_id)->email;
            //remote current office mail
            $key = array_search($currentDocumentEmail, $documentMailTo);
            if ($key !== false) {
                unset($documentMailTo[$key]);
            }
            $store = $storeRepo->findOrFail($request->store_id);
            $documentMailTo[] = $store->email;
            $documentMailTo = implode(',', array_unique($documentMailTo));
            $updateMailDocument = [
                'to' => $documentMailTo
            ];

            $mailDocumentRepo->update($mailDocument->id, $updateMailDocument);

        }
        return $this->update($id, $dataDocument);
    }

    public function isTemplate($document)
    {
        return $document->documentType->code == Constant::DOCUMENT_IS_TEMPLATE;
    }

    public function isOwner($document)
    {
        return $document->owner_id == auth()->user()->id;
    }

    private static function getOfficeByCodition($offices = [], $divisions = [], $branchs = [])
    {
        if ($offices && !empty($offices)) {
            return Store::whereIn('office_id', explode(',', $offices))
                ->get()
                ->pluck('id')
                ->toArray();
        }
        if ($divisions && !empty($divisions)) {
            return Store::whereIn('office_id', function ($query) use ($divisions) {
                $query->select('id')
                    ->from(with(new Office)->getTable())
                    ->whereIn('division_id', explode(',', $divisions));
            })->get()
                ->pluck('id')
                ->toArray();
        }

        if ($branchs && !empty($branchs)) {
            return Store::whereIn('office_id', function ($query) use ($branchs) {
                $query->select('id')
                    ->from(with(new Office)->getTable())
                    ->whereIn('division_id', function ($q) use ($branchs) {
                        $q->select('id')
                            ->from(with(new Division)->getTable())
                            ->whereIn('branch_id', explode(',', $branchs));
                    });
            })->get()
                ->pluck('id')
                ->toArray();
        }

        return [];
    }

    public function search($param)
    {
        $userInfo = auth()->user();
        $document = $this->model;
        //search with freeword
        if (isset($param['free_word']) && !empty($param['free_word'])) {
            $document = $document->where(function ($query) use ($param) {
                foreach ($param['free_word'] as $key => $item) {
                    $item['text_fullwidth'] = convertToFullwidth($item['text']);
                    $item['text_halfwidth'] = convertToHalfwidth($item['text']);
                    if ($key == 0 || $item['condition'] == 'and') {
                        $query->where(function ($q) use ($item) {
                            $q->searchName($item['text_fullwidth'], $item['text_halfwidth'])
                                ->searchPartnerName($item['text_fullwidth'], $item['text_halfwidth'])
                                ->searchOriginalName($item['text_fullwidth'], $item['text_halfwidth'])
                                ->searchFileName($item['text_fullwidth'], $item['text_halfwidth']);
                        });
                    } else {
                        $query->orWhere(function ($q) use ($item) {
                            $q->searchName($item['text_fullwidth'], $item['text_halfwidth'])
                                ->searchPartnerName($item['text_fullwidth'], $item['text_halfwidth'])
                                ->searchOriginalName($item['text_fullwidth'], $item['text_halfwidth'])
                                ->searchFileName($item['text_fullwidth'], $item['text_halfwidth']);
                        });
                    }
                }
            });
        }

        //search with tag
        if (isset($param['tag_ids']) && $param['tag_ids']) {
            $document = $document->searchTagName(explode(',', $param['tag_ids']));
        }

        //search with organization
        if (isset($param['store_ids']) && $param['store_ids']) {
            $stores = $param['store_ids'];
        } elseif (isset($param['office_ids']) && $param['office_ids']) {
            $stores = $this->getOfficeByCodition($param['office_ids']);
        } elseif (isset($param['division_ids']) && $param['division_ids']) {
            $stores = $this->getOfficeByCodition([], $param['division_ids']);
        } elseif (isset($param['branch_ids']) && $param['branch_ids']) {
            $stores = $this->getOfficeByCodition([], [], $param['branch_ids']);
        } else {
            $stores = '';
        }
        if ($stores && !empty($stores)) {
            if (!is_array($stores)) {
                $stores = explode(',', $stores);
            }
            $document = $document->whereIn('store_id', $stores);
        }

        //search with user create
        if (isset($param['owner_ids']) && $param['owner_ids']) {
            $document = $document->whereIn('owner_id', explode(',', $param['owner_ids']));
        }

        //search with document type
        if (isset($param['document_type_ids']) && $param['document_type_ids']) {
            $document = $document->whereIn('document_type_id', explode(',', $param['document_type_ids']));
        }

        //search with updatee at
        if (isset($param['updated_at_from']) && $param['updated_at_from']) {
            $document = $document->whereDate('updated_at', '>=', $param['updated_at_from']);
        }
        if (isset($param['updated_at_to']) && $param['updated_at_to']) {
            $document = $document->whereDate('updated_at', '<=', $param['updated_at_to']);
        }

        //permission
        if ($userInfo->role->code == Role::STAFF) {
            $document = $document->permission($userInfo->store_id, $userInfo->position_id);
        }

        //search with contract date
        if (isset($param['date_contract_from']) && $param['date_contract_from']) {
            $document = $document->contractDate($param['date_contract_from']);
        }
        if (isset($param['date_contract_to']) && $param['date_contract_to']) {
            $document = $document->contractDate(null, $param['date_contract_to']);
        }

        if (isset($param['with_trashed']) && $param['with_trashed']) {
            $document = $document->withTrashed();
        }

        return $document->orderBy('id', 'DESC')->paginate(isset($param['limit']) ? $param['limit'] : 9999999);
    }

    public function setDocumentAccess($document, $params)
    {
        $positionRepository = new PositionRepository();
        $dataSave = [];
        if (isset($params['role_setting'])) {
            foreach ($params['role_setting'] as $roleSetting) {
                if (empty ($roleSetting['positions'])) {
                    $roleSetting['positions'] = $positionRepository->getAllPositionStaff()->pluck('id')->toArray();
                }
                $dataSave[$roleSetting['store']]['positions_id'] = implode(',', $roleSetting['positions']);
            }
        }
        if (!empty($dataSave)) {
            $document->storePermission()->sync($dataSave);
        } else {
            $document->storePermission()->detach();
        }
    }

    public function checkPermissionDocument($documentId, $isUpdate = 0)
    {
        $hasPermission = false;
        $documentIDS = [];
        $document = $this->findOrFailDocument($documentId);
        //get all document with servicer
        if ($document->service_user_id) {
            $documentIDS = $this->model->where('service_user_id', $document->service_user_id)
                ->pluck('id')
                ->toArray();
        }

        $roleRepository = new RoleRepository();
        $roleCurrentUser = $roleRepository->findOrFail(auth()->user()->role_id);

        $documentOfficeRepository = new DocumentPermissionRepository();
        $documentPermission = $documentOfficeRepository->getDocumentPermission($document->id);

        if ($isUpdate) {
            $documentTypeRepo = new DocumentTypeRepository();
            $documentObjectRepo = new DocumentObjectRepository();
            $documentTypeInfo = $documentTypeRepo->find($document->document_type_id);
            $documentObjectInfo = $document->document_object_id
                ? $documentObjectRepo->find($document->document_object_id)
                : 0;
            if (
                isset(DocumentType::DOCUMENT_COMMON[$documentTypeInfo->code])
                && in_array($documentObjectInfo->code, DocumentType::DOCUMENT_COMMON[$documentTypeInfo->code])
            ) {
                $hasPermission = true;
            } elseif ($documentPermission) {
                $arrPositions = explode(',', $documentPermission->positions_id);
                if (in_array(auth()->user()->position_id, $arrPositions)) {
                    $hasPermission = true;
                }
            }
        } else {
            if (in_array($documentId, $documentIDS)) {
                $hasPermission = true;
            } else {
                if ($documentPermission) {
                    $arrPositions = explode(',', $documentPermission->positions_id);
                    if (in_array(auth()->user()->position_id, $arrPositions)) {
                        $hasPermission = true;
                    }
                }
            }
        }

        if ($roleCurrentUser->code != Role::STAFF || ($roleCurrentUser->code == Role::STAFF && $hasPermission)) {
            return true;
        }
        return false;
    }

    public function getAllPositionOfStaff()
    {
        $roleRepository = new RoleRepository();
        $roleStaff = $roleRepository->getRoleIsStaff();
        return $roleStaff->positions()->pluck('id')->toArray();
    }

    public function saveFileSetManagement($params)
    {
        $fileSetManagementRepo = new FileSetManagementRepository();
        $dataFileSetManage = [
            'store_id' => $params['store_id'],
            'document_type_id' => $params['document_type_id'],
            'service_user_id' => $params['service_user_id'],
        ];

        //check exits file set
        $fileSetManagement = $fileSetManagementRepo
            ->checkExitsFileSet($params['service_user_id'], $params['document_type_id'], $params['store_id']);

        if (empty($fileSetManagement)) {
            return $fileSetManagementRepo->create($dataFileSetManage);
        }

        return $fileSetManagement;
    }

    public function getAllFreeDocument()
    {
        if (auth()->user()->isStaff()) {
            return $this->model
                ->select(DB::raw('documents.*'))
                ->join('document_permission', 'document_permission.document_id', '=', 'documents.id')
                ->where('document_permission.store_id', auth()->user()->store_id)
                ->where(DB::raw('CONCAT(",", positions_id, ",")'), 'like', '%,' . auth()->user()->position_id . ',%')
                ->whereNull('documents.folder_id')
                ->get();
        }
        return $this->model->whereNull('folder_id')->get();
    }

    public function prepareDataToSave($params)
    {
        $dataDocument = $params->only([
            'service_user',
            'partner_name',
            'name',
        ]);
        $dataDocument['store_id'] = $params->store_id;
        if (auth()->user()->isStaff() && !empty(auth('api')->user()->store_id)) {
            $dataDocument['store_id'] = auth('api')->user()->store_id;
        }

        if (isset($params->folder_id) && !empty($params->folder_id)) {
            $dataDocument['folder_id'] = $params->folder_id;
        }

        // filter info partner of document
        if (in_array($params->document_type, Constant::IS_B2C)) {
            unset($dataDocument['partner_name']);
        } elseif ($params->document_type == Constant::DOCUMENT_IS_TEMPLATE) {
            unset($dataDocument['service_user']);
        } else {
            unset($dataDocument['service_user']);
            unset($dataDocument['partner_name']);
        }
        // find document type by code
        $documentTypeRepository = new DocumentTypeRepository();
        $documentType = $documentTypeRepository->findByCode($params->document_type);
        $dataDocument['document_type_id'] = $documentType->id;

        // find document object by code
        if (isset($params->document_object)) {
            $documentObjectRepository = new DocumentObjectRepository();
            $documentObject = $documentObjectRepository->findByCode($params->document_object);
            $dataDocument['document_object_id'] = $documentObject->id;
        }

        $dataDocument['owner_id'] = auth('api')->user()->id;
        return $dataDocument;
    }

    public function getDocumentsInFileSet($serviceUserId, $documentTypeId = null, $officeId = null, $isOnly = false)
    {
        $query = $this->model->where('service_user_id', $serviceUserId);
        if ($documentTypeId) {
            $query = $query->where('document_type_id', $documentTypeId);
        }
        if ($officeId) {
            $query = $query->where('store_id', $officeId);
        }
        if ($isOnly) {
            return $query->first();
        }

        return $query->get();
    }

    public function getParentsOfDocuments($documentsId)
    {
        return $this->model->whereIn('id', $documentsId)->pluck('folder_id')->toArray();
    }

    public function findOrFailDocument($id)
    {
        try {
            $result = $this->model->findOrFail($id);
        } catch (\Exception $e) {
            throw new ModelNotFoundException(__('message.document_not_found'), 0);
        }

        return $result;
    }

    public function documentsIdNotSetAccess($exceptDocumentsId)
    {
        return $this->model->whereNotIn('id', $exceptDocumentsId)->pluck('id')->toArray();
    }

    public function getDocumentByID($ids)
    {
        return $this->model->whereIn('id', $ids)
            ->with(['attributes', 'documentType', 'documentObject', 'serviceUser', 'basicStore'])
            ->get();
    }

    public function getDocumentImportantMatter($exchange_deadline = '')
    {
        return $this->model
            ->with(['mailDocument', 'office', 'documentType', 'documentObject', 'serviceUser.fileSetManagements'])
            ->whereHas('documentObject', function ($query) {
                $query->whereCode(DocumentObject::IMPORTANT_MATTER_EXPLANATION);
            })
            ->when($exchange_deadline, function ($query) use ($exchange_deadline) {
                $exchange_deadline = formatDate($exchange_deadline);
                $query
                    ->whereDoesntHave('files.histories', function ($query) use ($exchange_deadline) {
                        $query->whereDate('created_at', '>=', $exchange_deadline);
                    });

            })
            ->whereHas('serviceUser.office', function ($query) {
                $query->whereNotNull('email');
            })
            ->get();
    }

    public function getDocumentByAlert()
    {
        return $this->model->whereIn('id', function ($query) {
            $query->select('document_id')
                ->from(with(new MailDocument)->getTable())
                ->where('type', MailDocument::SEND_TYPE_AUTO);
        })->whereIn('id', function ($query) {
            $query->select('document_id')
                ->from(with(new DocumentAttribute)->getTable())
                ->where('attribute_id', function ($q) {
                    $q->select('id')
                        ->from(with(new Attribute)->getTable())
                        ->where('code', Attribute::VALIDITY_PERIOD);
                })->whereNotNull('end_date');
        })->with(['attributes', 'documentType', 'documentObject', 'serviceUser.fileSetManagements', 'basicStore'])
            ->get();
    }

    public function getDocumentPermission()
    {
        return $this->model->with('folder.parent')->whereHas('documentPermissionUserStaff')->get()->toArray();
    }

    public function getDocWithDocTypeDocObj($params)
    {
        $serviceUser = json_decode($params['service_user'], 1);
        return $this->model->where('document_object_id', $params['document_object'])
            ->where('service_user_id', $serviceUser['id'])->first();
    }

    public function getDocumentByIDWithoutAttr($ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function searchDeletedFiles($params)
    {
        $document = $this->model;
        return $document->getDeletedFile()->paginate(isset($params['limit']) ? $params['limit'] : 9999999);
    }

    public function getDocumentsByServiceUserIds($serviceUserIds)
    {
        return $this->model->whereIn('service_user_id', $serviceUserIds)->pluck('id')->toArray();
    }
}
