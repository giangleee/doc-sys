<?php


namespace App\Repositories;

use App\Helper\Constant;
use App\Models\DocumentObject;
use App\Models\DocumentType;
use App\Models\Folder;
use App\Models\IsUserAdmin;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FolderRepository extends BaseRepository
{
    public function getModel()
    {
        return Folder::class;
    }

    private function getChildrenServiceUser($id, $folderQuery)
    {
        $folderChildrenServiceUser = [];
        if ($folderQuery->service_user_id && $folderQuery->is_system == Folder::IS_SYSTEM) {
            //get all folder service user
            $folderServiceUser = $this->model->where('service_user_id', $folderQuery->service_user_id)
                ->where('is_system', Folder::IS_SYSTEM)
                ->get()->pluck('id')->toArray();

            //get all children folder service user
            if (!empty($folderServiceUser)) {
                $folderChildrenServiceUser = $this->model->whereIn('parent_id', $folderServiceUser)
                    ->orderBy('is_common', 'DESC')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->load([
                        'children',
                        'documents' => function ($query) {
                            $query->select(
                                'document_type_id',
                                'id',
                                'folder_id',
                                'name',
                                'store_id',
                                'owner_id',
                                'service_user_id'
                            );
                        },
                        'documents.documentType' => function ($query) {
                            $query->select('code', 'id', 'name', 'pattern_type', 'type');
                        },
                        'documents.children' => function ($query) {
                            $query
                                ->select('document_id', 'file_format', 'id', 'original_name');
                        },
                    ])->toArray();
                foreach ($folderChildrenServiceUser as $key => $item) {
                    $folderChildrenServiceUser[$key]['parent_id'] = (int)$id;
                    if (empty($folderChildrenServiceUser[$key]['children'])) {
                        $folderChildrenServiceUser[$key]['children'] = 0;
                    }
                    if (empty($folderChildrenServiceUser[$key]['documents'])) {
                        $folderChildrenServiceUser[$key]['documents'] = 0;
                    }
                }
            }
        }

        return $folderChildrenServiceUser;
    }

    public function show($id)
    {
        //folder service user
        $folderQuery = $this->model->findOrFail($id);
        $folderChildrenServiceUser = $this->getChildrenServiceUser($id, $folderQuery);

        if (auth()->user()->isStaff()) {
            // $folderQuery = $this->model->findOrFail($id);
            if ($folderQuery->store_id == auth()->user()->store_id) {
                $folder = $folderQuery->load([
                    'children' => function ($query) {
                        $query->withCount('children as children')->withCount('getDocumentPermission as documents');
                    },
                    'documents' => function ($query) {
                        $query->whereHas('documentPermissionUserStaff')
                            ->select(
                                'document_type_id',
                                'id',
                                'folder_id',
                                'name',
                                'store_id',
                                'owner_id',
                                'service_user_id'
                            );
                    },
                    'documents.documentType' => function ($query) {
                        $query->select('code', 'id', 'name', 'pattern_type', 'type');
                    },
                    'documents.children' => function ($query) {
                        $query
                            ->select('document_id', 'file_format', 'id', 'original_name');
                    },
                ])->toArray();
            } else {
                $folder = $folderQuery->load([
                    'children',
                    'childrenFolder',
                    'documents' => function ($query) {
                        $query->whereHas('documentPermissionUserStaff')
                            ->select(
                                'document_type_id',
                                'id',
                                'folder_id',
                                'name',
                                'store_id',
                                'owner_id',
                                'service_user_id'
                            );
                    },
                    'documents.documentType' => function ($query) {
                        $query->select('code', 'id', 'name', 'pattern_type', 'type');
                    },
                    'documents.children' => function ($query) {
                        $query
                            ->select('document_id', 'file_format', 'id', 'original_name');
                    },
                ])->toArray();
                $folderChildrenPermission = [];
                foreach ($folder['children_folder'] as $item) {
                    if (
                        count($item['documents'])
                        || $item['store_id'] == auth()->user()->store_id
                    ) {
                        array_push($folderChildrenPermission, $item);
                    } else {
                        $tree = $this->flattenTree($item['children_folder']);
                        $checkHasDocument = collect($tree)->whereNotNull('version')->all();
                        if (count($checkHasDocument)) {
                            array_push($folderChildrenPermission, $item);
                        }
                    }
                }
                $idFolderChildrenPermission = collect($folderChildrenPermission)->pluck('id')->all();
                $folderChild = [];
                foreach ($folder['children'] as $key => $value) {
                    if (in_array($value['id'], $idFolderChildrenPermission)) {
                        $folderDetail = collect($folderChildrenPermission)
                            ->where('id', '=', $value['id'])->first();
                        $folder['children'][$key]['children'] =
                            isset($folderDetail['children_folder']) ? count($folderDetail['children_folder']) : 0;
                        $folder['children'][$key]['documents'] =
                            isset($folderDetail['documents']) ? count($folderDetail['documents']) : 0;
                        array_push($folderChild, $folder['children'][$key]);
                    }
                }
                $folderChildId = collect($folderChild)->pluck('id')->toArray();
                if ($folder['office_id'] == auth()->user()->store->office_id) {
                    $storeFolder = collect($folder['children_folder'])
                        ->where('store_id', '=', auth()->user()->store_id)->first();
                    if (!in_array($storeFolder['id'], $folderChildId)) {
                        $storeFolder['children'] =
                            isset($storeFolder['children_folder']) ? count($storeFolder['children_folder']) : 0;
                        $storeFolder['documents'] =
                            isset($storeFolder['children_folder']) ? count($storeFolder['documents']) : 0;
                        array_push($folderChild, $storeFolder);
                    }
                }
                if ($folder['division_id'] == auth()->user()->store->office->division_id) {
                    $officeFolder = collect($folder['children_folder'])
                        ->where('office_id', '=', auth()->user()->store->office_id)->first();
                    if (!in_array($officeFolder['id'], $folderChildId)) {
                        $officeFolder['children'] =
                            isset($officeFolder['children_folder']) ? count($officeFolder['children_folder']) : 0;
                        $officeFolder['documents'] =
                            isset($officeFolder['children_folder']) ? count($officeFolder['documents']) : 0;
                        array_push($folderChild, $officeFolder);
                    }

                }
                if ($folder['branch_id'] == auth()->user()->store->office->division->branch_id) {
                    $divisionFolder = collect($folder['children_folder'])
                        ->where('division_id', '=', auth()->user()->store->office->division_id)->first();
                    if (!in_array($divisionFolder['id'], $folderChildId)) {
                        $divisionFolder['children'] =
                            isset($divisionFolder['children_folder']) ? count($divisionFolder['children_folder']) : 0;
                        $divisionFolder['documents'] =
                            isset($divisionFolder['children_folder']) ? count($divisionFolder['documents']) : 0;
                        array_push($folderChild, $divisionFolder);
                    }
                }
                unset($folder['children_folder']);
                $folder['children'] = $folderChild;
            }

            if (!empty($folderChildrenServiceUser)) {
                $folder['children'] = $folderChildrenServiceUser;
            } else {
                foreach ($folder['children'] as $key => $item) {
                    if ($item['service_user_id']) {
                        $queryFolderSu = $this->model->findOrFail($item['id']);
                        $folder['children'][$key]['children'] = $this->getChildrenServiceUser($item['id'], $queryFolderSu);
                    }
                }
            }

            return $folder;
        }

        $folder = $this->model->with([
            'children' => function ($query) {
                $query->withCount('children as children')->withCount('documents as documents');
            },
            'documents' => function ($query) {
                $query->select(
                    'document_type_id',
                    'id',
                    'folder_id',
                    'name',
                    'store_id',
                    'owner_id',
                    'service_user_id'
                );
            },
            'documents.documentType' => function ($query) {
                $query->select('code', 'id', 'name', 'pattern_type', 'type');
            },
            'documents.children' => function ($query) {
                $query->select('document_id', 'file_format', 'id', 'original_name');
            },
        ])->findOrFail($id)->toArray();
        if (!empty($folderChildrenServiceUser)) {
            $folder['children'] = $folderChildrenServiceUser;
        } else {
            foreach ($folder['children'] as $key => $item) {
                if ($item['service_user_id']) {
                    $queryFolderSu = $this->model->findOrFail($item['id']);
                    $folder['children'][$key]['children'] = $this->getChildrenServiceUser($item['id'], $queryFolderSu);
                }
            }
        }

        return $folder;
    }

    private function changeKeyRecursive(array $input, $currentKey, $newKey)
    {
        $return = array();
        foreach ($input as $key => $value) {
            if ($key === $currentKey) {
                $key = $newKey;
            }
            if (is_array($value)) {
                $value = self::changeKeyRecursive($value, $currentKey, $newKey);
            }
            $return[$key] = $value;
        }
        return $return;
    }

    public function getList($request)
    {
        $model = $this->model->query();

        if ($request->service_user_id) {
            $respone = $this->getFolderOfServiceUser($request);
            return $respone;
        }
        //get child folders from parent_id
        if ($request->parent_id) {
            $childs = $this->model->withCount('childrens')->where('parent_id', $request->parent_id)->get()->toArray();
            return [$childs];
        }

        //get folders of staff
        if (auth()->user()->isStaff() && !empty(auth()->user()->store_id)) {
            $respone = $this->getFolderOfOfficeStaff(auth()->user()->store_id);
            return $respone;
        }

        // get child folders of root
        $respone = $model->withCount('childrens')->whereNull('parent_id')->get()->toArray();
        $data = [];
        // get tree folders of document
        if ($request->document_id) {
            $folder = $this->model
                ->withCount('childrens')
                ->join('documents', 'documents.folder_id', 'folders.id')
                ->where('documents.id', $request->document_id)
                ->select('folders.*')->first();
            if ($folder) {
                $data = $this->getParentAndChildOfParentFolder($folder->parent_id);
            }
        }
        array_push($data, $respone);
        return array_reverse($data);
    }

    public function getParentAndChildOfParentFolder($parent_id, $i = 0, $list = [], $skipIds = [])
    {
        if (!$parent_id) {
            $list[$i] = $this->model->withCount('childrens')->whereNull('parent_id')->get()->toArray();
            return $list;
        }
        $childs = $this->model->withCount('childrens')->where('parent_id', $parent_id)->get()->toArray();
        $list[$i] = $childs;
        $parent = $this->model->where('id', $parent_id)->first();
        if ($parent->parent_id && !in_array($parent->id, $skipIds)) {
            $i++;
            return $this->getParentAndChildOfParentFolder($parent->parent_id, $i, $list, $skipIds);
        } else {
            return $list;
        }
    }

    /**
     * get folder of office's staff
     */
    public function getFolderOfOfficeStaff($storeId)
    {
        $foldersOffice = $this->model->withCount('childrens')
            ->where('store_id', $storeId)
            ->where('is_system', Folder::IS_SYSTEM)
            ->get()->toArray();
        $allIds = array_column($foldersOffice, 'id');
        $folders = [];
        $ids = [];
        foreach ($foldersOffice as $folder) {
            if (!in_array($folder['parent_id'], $allIds)) {
                $folders[] = $folder;
                $ids[] = $folder['id'];
            }
        }
        $list = [];
        if (count($folders)) {
            $list = $this->getParentsExcludeChild($folders[0]['parent_id']);
            $list = array_reverse($list);
            $list[] = [$folders[0]];
        }

        $folders = [];
        if (isset(request()->document_id) && request()->document_id) {
            $documentID = request()->document_id;
            $folderDocumment = $this->model
                ->withCount('childrens')
                ->join('documents', 'documents.folder_id', '=', 'folders.id')
                ->where('documents.id', $documentID)
                ->select('folders.*')->first();
            // $folderDocumment
            if ($folderDocumment
                && (in_array($folderDocumment->parent_id, $ids)
                    || in_array($folderDocumment->id, $ids))
            ) {
                $folders = $this->getParentAndChildOfParentFolder($folderDocumment->parent_id, 0, [], $ids);
                $folders = array_reverse($folders);
                $list = array_merge($list, $folders);
            } else {
                if ($folderDocumment && $folderDocumment->parent_id) {
                    $folders = $this->getParentsExcludeChild($folderDocumment->parent_id);
                    $folders = array_reverse($folders);
                    foreach ($folders as $key => $foldersIn) {
                        if (!isset($list[$key])) {
                            $list[$key] = $foldersIn;
                            continue;
                        }
                        $idsInList = array_column($list[$key], 'id');
                        if (!in_array($foldersIn[0]['id'], $idsInList)) {
                            array_push($list[$key], $foldersIn[0]);
                        }
                    }
                    array_push($list, [$folderDocumment]);
                }
            }
        }
        return $list;
    }

    public function getParentsExcludeChild($parent_id, $list = [], $i = 0)
    {
        $parent = $this->model->withCount('childrens')->where('id', $parent_id)->first()->toArray();
        $parent['disabled'] = true;
        $list[$i] = [$parent];
        if ($parent['parent_id']) {
            $i++;
            return $this->getParentsExcludeChild($parent['parent_id'], $list, $i);
        } else {
            return $list;
        }
    }


    public function getFolderOfServiceUser($request)
    {
        $data = $request->only('branch_id', 'division_id', 'office_id', 'service_user_id', 'store_id');
        //get parents of su folder
        $foldersParent = $this->model
            ->withCount('childrens')
            ->where(function ($query) use ($data) {
                return $query
                    ->where('branch_id', $data['branch_id'])
                    ->orWhere('division_id', $data['division_id'])
                    ->orWhere('office_id', $data['office_id'])
                    ->orWhere('store_id', $data['store_id']);
            })
            ->whereNull('document_type_id')
            ->whereNull('service_user_id')
            ->orderBy('id', 'asc')
            ->get();
        $storeFolder = $foldersParent[3];
        $foldersParent = [
            [$foldersParent[0]],
            [$foldersParent[1]],
            [$foldersParent[2]],
            [$foldersParent[3]],
        ];
        $foldersServiceUser = $this->model
            ->with('childrens')
            ->where('service_user_id', $data['service_user_id'])
            ->whereNull('document_type_id')
            ->get()->toArray();
        $childs = [];
        $serviceUserId = $data['service_user_id'];
        if (count($foldersServiceUser)) {
            $folderSU = null;

            foreach ($foldersServiceUser as $folder) {
                if (!$folderSU && $folder['store_id'] == $data['store_id']) {
                    $folderSU = $folder;
                }
                $storeId = $folder['store_id'];
                $folderChilds = array_map(function ($f) use ($storeId, $serviceUserId) {
                    $f['store_id'] = $storeId;
                    $f['service_user_id'] = $serviceUserId;
                    return $f;
                }, $folder['childrens']);
                $childs = array_merge($childs, $folderChilds);
            }
            if (!$folderSU) {
                $folderSU = $this->createFolderSU($storeFolder, $serviceUserId);
            }
        } else {
            $folderSU = $this->createFolderSU($storeFolder, $serviceUserId);
        }

        // $master
        $folSUId = $folderSU['id'];
        $folderSU['childrens_count'] = count($childs);
        $childs = array_map(function ($f) use ($folSUId) {
            $f['parent_id'] = $folSUId;
            return $f;
        }, $childs);

        array_push($foldersParent, [$folderSU], $childs);
        return $foldersParent;
    }


    public function createFolderSU($storeFolder, $serviceUserId)
    {
        $serviceUserRepository = new ServiceUserRepository();
        $serviceUser = $serviceUserRepository->find($serviceUserId);
        $folderSU = new Folder();
        $folderSU->store_id = $storeFolder['store_id'];
        $folderSU->owner_id = User::IS_USER_SYSTEM_ADMIN;
        $folderSU->name = $serviceUser['code'] . 'ー' . $serviceUser['name'];
        $folderSU->service_user_id = $serviceUser['id'];
        $folderSU->is_system = Folder::IS_SYSTEM;
        $folderSU->parent_id = $storeFolder->id;
        $folderSU->save();
        return $folderSU;
    }

    public function getListChildSU($ids)
    {
        return $this->model
            ->withCount('childrens')
            ->whereIn('parent_id', $ids)
            ->get()->toArray();
    }

    public function findOrFailFolder($id)
    {
        try {
            $result = $this->model->findOrFail($id);
            return $result;
        } catch (\Exception $e) {
            throw new ModelNotFoundException(__('message.folder_not_found'), 0);
        }


    }

    public function isOwner($folder)
    {
        return $folder->owner_id == auth()->user()->id;
    }

    public function getFolderByBranch()
    {
        return $this->model->whereNotNull('branch_id')
            ->where('is_system', Folder::IS_SYSTEM)
            ->get()
            ->pluck('id', 'branch_id')
            ->toArray();
    }

    public function getFolderByDivision()
    {
        return $this->model->whereNotNull('division_id')
            ->where('is_system', Folder::IS_SYSTEM)
            ->get()
            ->pluck('id', 'division_id')
            ->toArray();
    }

    public function getFolderByOffice()
    {
        return $this->model->whereNotNull('office_id')
            ->where('is_system', Folder::IS_SYSTEM)
            ->get()
            ->pluck('id', 'office_id')
            ->toArray();
    }

    public function getFolderOfficeOfStaff()
    {
        return $this->model->where('store_id', auth()->user()->store_id)
            ->where('is_system', Folder::IS_SYSTEM)
            ->first();
    }

    public function getFolderByParentIdExceptId($parentId, $id)
    {
        return $this->model->where('parent_id', $parentId)->where('id', '<>', $id)->get();
    }

    public function getFoldersOwned()
    {
        return $this->model->where('owner_id', auth()->user()->id)->pluck('id')->toArray();
    }

    public function getFoldersByStoreId($storeId)
    {
        return $this->model->where('store_id', $storeId)->get();
    }

    private function flattenTree($array)
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $result[] = array_filter($item, function ($array) {
                    return !is_array($array);
                });
                $result = array_merge($result, self::flattenTree($item));
            }
        }
        return array_filter($result);
    }

    private function getDivisionOfDocument($documents)
    {
        $results = [];
        foreach ($documents as $document) {
            if ($document['folder']) {
                if ($document['folder']['parent'] && $document['folder']['parent']['parent'] == null) {
                    $results['division_id'][] = $document['folder']['parent']['id'];
                }
                $tree = collect($this->flattenTree($document['folder']))
                    ->where('division_id', '!=', null)
                    ->first();
                if ($tree) {
                    $results['division_id'][] = $tree['division_id'];
                }
                $results['folder'][] = $this->flattenTree($document['folder']);
                if ($document['folder']['branch_id'] && $document['folder']['parent'] == null) {
                    $results['documents'][] = $document;
                }
                if (
                    !$document['folder']['branch_id'] && $document['folder']['parent'] == null
                    && !$document['folder']['office_id'] && !$document['folder']['division_id']
                ) {
                    $results['documents'][] = $document;
                }
            }
        }
        return [
            'division_id' => isset($results['division_id']) ? array_unique($results['division_id']) : [],
            'document_id' => isset($results['documents']) ? collect($results['documents'])->pluck('id')->all() : [],
            'folder' => isset($results['folder']) ? $results['folder'] : []
        ];
    }

    public function getTreeFolder()
    {
        if (auth()->user()->isStaff()) {
            $documentRepository = new DocumentRepository();

            $documents = $documentRepository->getDocumentPermission();
            $divisionOfDocument = $this->getDivisionOfDocument($documents);
            if (auth()->user()->division_id) {
                $divisionOfDocument['division_id'][] = auth()->user()->division_id;
            }
            return $this->model
                ->with([
                    'children' => function ($query) use ($divisionOfDocument) {
                        $query->whereIn('division_id', $divisionOfDocument['division_id']);
                    },
                    'documents' => function ($query) use ($divisionOfDocument) {
                        $query->whereIn('id', $divisionOfDocument['document_id']);
                    },
                ])->whereNull('parent_id')
                ->where(function ($query) use ($divisionOfDocument) {
                    $query->whereHas('documents', function ($query) use ($divisionOfDocument) {
                        $query->whereIn('id', $divisionOfDocument['document_id']);
                    })->orWhereHas('children', function ($query) use ($divisionOfDocument) {
                        $query->whereIn('division_id', $divisionOfDocument['division_id']);
                    });
                })
                ->get();
        }
        return $this->model->whereNull('parent_id')->with('children')->get();
    }

    public function getFolderByOfficeID($officeID)
    {
        return $this->model->where('office_id', $officeID)
            ->orderBy('id', 'ASC')
            ->first();
    }

    public function getFolderOfUser()
    {
        return $this->model->with('parent')->whereOfficeId(auth()->user()->office_id)->get()->toArray();
    }

    public function checkExistFolderServiceUser($officeID, $serviceUserID = null)
    {
        $query = $this->model->where('office_id', $officeID)->where('is_system', Folder::IS_SYSTEM);

        if (is_null($serviceUserID)) {
            $query->whereNull('service_user_id');
        } else {
            $query->where('service_user_id', $serviceUserID);
        }

        return $query->first();
    }

    /** find or create folder of service user */
    public function findOrCreateFolderForServiceUser($params)
    {
        $documentTypeRepo = new DocumentTypeRepository;
        $documentType = $documentTypeRepo->findByCode($params['document_type']);
        $commonOfType = DocumentObject::COMMON_OBJECT[$params['document_type']];
        //is common folder
        $common = in_array((int)$params['document_object'], $commonOfType);
        $findServiceUserFolder = $this->findServiceUserFolder($params, $common, $documentType->id);
        if ($findServiceUserFolder['exist_service_user_folder']) {
            if ($findServiceUserFolder['folder']) {
                return $findServiceUserFolder['folder'];
            } else {
                return $this->createChildFolderForSU(
                    $params,
                    $findServiceUserFolder['exist_service_user_folder'],
                    $common,
                    $documentType
                );
            }
        } else {
            return $this->createFolderForSU($params['store_id'], $params, $common, $documentType);
        }
    }


    public function checkFolderSU($params)
    {
        $folderId = $params['folder_id'];
        $folder = $this->model->find($folderId);
        $commonOfType = DocumentObject::COMMON_OBJECT[$params['document_type']];
        //is common folder
        $common = in_array((int)$params['document_object'], $commonOfType);
        if (!$folder->service_user_id) {
            if ($folder->is_common == $common || $folder->document_type_id) {
                return $folder;
            }
        } else {
            if ($folder->is_common == $common || $folder->document_type_id) {
                return $folder;
            }
        }
        return $this->findOrCreateFolderForServiceUser($params);
    }

    public function getListWithBranchId($officeId)
    {
        $officeRepo = new OfficeRepository;
        //get info
        $office = $officeRepo->find($officeId);
        $divisionId = $office->division->id;
        $branchId = $office->division->branch->id;

        //create array tree
        $folderOfBracnh = $this->model->where('branch_id', $branchId)->pluck('id')->toArray();
        $folderOfDivision = $this->model->where('division_id', $divisionId)->pluck('id')->toArray();
        $folderOfOffice = $this->model->where('office_id', $office->id)->pluck('id')->toArray();
        $folderOfServiceUser = $this->model->whereIn('parent_id', $folderOfOffice)->where(function ($q) {
            $q->whereNotNull('is_common')->orWhereNotNull('document_type_id');
        })->pluck('id')->toArray();
        $folderIds = array_unique(
            array_merge($folderOfBracnh, $folderOfDivision, $folderOfOffice, $folderOfServiceUser)
        );
        Constant::$foldersIdToFilter = $folderIds;

        //return array tree
        $model = $this->model->query();
        $model->whereIn('id', $folderIds);
        $respone = $model->with('office', 'childrens')->whereNull('parent_id')->get()->toArray();
        $respone = $this->changeKeyRecursive($respone, 'childrens', 'children');
        return $respone;
    }

    public function checkFolder($params)
    {
        $folder = $this->model->find($params['folder_id']);
        $serviceUser = json_decode($params['service_user'], 1);
        $documentTypeRepository = new DocumentTypeRepository();
        $documentType = $documentTypeRepository->findByCode($params['document_type']);

        //create when folder service user, folder doc type and folder common not existed
        if (empty($folder->is_common) && empty($folder->document_type_id)) {
            $dataCreateFolder = [
                'service_user_id' => $serviceUser['id'],
                'service_user_name' => $serviceUser['name'],
                'service_user_code' => $serviceUser['code'],
                'office_id' => $folder->office->id,
                'document_type_id' => $documentType->id,
                'document_type' => $params['document_type'],
                'office_name' => $folder->office->name,
                'document_object' => $params['document_object']
            ];
            return $this->findOrCreateFolderForServiceUser($dataCreateFolder);
        }

        //return id folder when exit folder doc type and common
        if (!empty($folder->is_common) || !empty($folder->document_type_id)) {
            return $folder;
        }
    }

    public function checkExistFolderCommon($serviceUserId)
    {
        //get folder service user
        $folderServiceUsers = $this->model->where('service_user_id', $serviceUserId)->pluck('id')->toArray();

        //get folder common
        $folderCommon = $this->model->whereIn('parent_id', $folderServiceUsers)
            ->whereNotNull('is_common')->pluck('id')->first();

        if (!empty($folderCommon)) {
            return true;
        }
        return false;
    }

    public function getFolderStore($storeId)
    {
        return $this->model->where('store_id', $storeId)
            ->whereNull('service_user_id')
            ->where('is_system', Folder::IS_SYSTEM)
            ->first();
    }

    public function updateNameFolderServiceUser($userID, $name)
    {
        return $this->model->where('service_user_id', $userID)
            ->update(['name' => $name]);

    }


    public function findServiceUserFolder($params, $isCommon = false, $documentTypeId = null)
    {

        $serviceUserFolders = $this->model
            ->where('service_user_id', $params['service_user_id'])
            ->where(['is_system' => Folder::IS_SYSTEM])
            ->whereNull('document_type_id')->get();

        if (!count($serviceUserFolders)) {
            return [
                'exist_service_user_folder' => false
            ];
        } else {
            $findFolder = null;
            $folders = $serviceUserFolders->toArray();
            $storeId = $params['store_id'];
            $serviceUserInFolderOffice = array_filter($folders, function ($folder) use ($storeId) {
                return $folder['store_id'] == $storeId;
            }, ARRAY_FILTER_USE_BOTH);
            $find = count($serviceUserInFolderOffice) ? array_values($serviceUserInFolderOffice)[0] : null;
            if ($isCommon) {
                $parentIds = array_column($folders, 'id');
                $findFolder = $this->model->whereIn('parent_id', $parentIds)
                    ->where('is_common', Folder::IS_COMMON)->first();

            } else {
                if (count($serviceUserInFolderOffice)) {
                    $suFolderIds = array_column($serviceUserInFolderOffice, 'id');
                    $findFolder = $this->model->whereIn('parent_id', $suFolderIds)
                        ->where('document_type_id', $documentTypeId)
                        ->first();
                }


            }
            return [
                'exist_service_user_folder' => $find,
                'folder' => $findFolder
            ];

        }

    }

    /**
     * create folder of SU  and subfolder
     * */
    public function createFolderForSU($storeId, $params, $common, $documentType)
    {

        $storeFolder = $this->model->where('store_id', $storeId)->first();

        $folderSU = new Folder();
        $folderSU->owner_id = User::IS_USER_SYSTEM_ADMIN;
        $folderSU->name = $params['service_user_code'] . 'ー' . $params['service_user_name'];
        $folderSU->service_user_id = $params['service_user_id'];
        $folderSU->is_system = Folder::IS_SYSTEM;
        $folderSU->parent_id = $storeFolder->id;
        $folderSU->store_id = $storeId;

        $folderSU->save();

        // child folder
        $dataChildFolderCommon = [
            'owner_id' => User::IS_USER_SYSTEM_ADMIN,
            'name' => $common ? Folder::NAME_FOLDER_COMMON : ($documentType->name . '(' . $params['store_name'] . ')'),
            'is_system' => Folder::IS_SYSTEM,
            'is_common' => $common,
            'document_type_id' => $common ? null : $documentType->id,
            'parent_id' => $folderSU->id
        ];
        return $this->model->create($dataChildFolderCommon);
    }

    /** create sub folder of SU is common or document_type */
    public function createChildFolderForSU($params, $parentFolder, $common, $documentType)
    {
        if (!isset($params['store_name'])) {
            $store = (new StoreRepository())->find($params['store_id']);
            $storeName = $store->name;
        } else {
            $storeName = $params['store_name'];
        }
        // child folder
        $dataChildFolderCommon = [
            'owner_id' => User::IS_USER_SYSTEM_ADMIN,
            'name' => $common ? Folder::NAME_FOLDER_COMMON : ($documentType->name . '(' . $storeName . ')'),
            'is_system' => Folder::IS_SYSTEM,
            'is_common' => $common,
            'document_type_id' => $common ? null : $documentType->id,
            'parent_id' => $parentFolder['id']
        ];
        return $this->model->create($dataChildFolderCommon);
    }

    public function filterWithTrashed($where)
    {
        return $this->model->where($where)->withTrashed()->first();
    }

    public function findFolderFileSet($serviceUserId, $documentTypeId, $officeId)
    {
        return $this->model->where('document_type_id', $documentTypeId)
            ->whereHas('nearestParent', function ($query) use ($serviceUserId, $officeId) {
                $query->where([
                    'service_user_id' => $serviceUserId,
                    'office_id' => $officeId
                ]);
            })->first();
    }

    public function getFolderStaffBelongsTo($conditions)
    {
        return $this->model->where($conditions)
            ->where('is_system', Folder::IS_SYSTEM)
            ->first();
    }

    public function getFolderByStoreID($store_id)
    {
        return $this->model->where('store_id', $store_id)
            ->where('is_system', Folder::IS_SYSTEM)
            ->orderBy('id', 'ASC')
            ->first();
    }
}
