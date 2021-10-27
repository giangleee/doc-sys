<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Http\Resources\FolderChildrenCollection;
use App\Http\Resources\FolderResource;
use App\Http\Resources\TreeFolderCollection;
use App\Models\Folder;
use App\Repositories\FolderRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    protected $folderRepository;
    protected $roleRepository;

    public function __construct(FolderRepository $folderRepository, RoleRepository $roleRepository)
    {
        $this->folderRepository = $folderRepository;
        $this->roleRepository = $roleRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $this->folderRepository->getList($request);
        $result = [];
        foreach ($data as $folderList) {
            $result[] = new FolderChildrenCollection($folderList);
        }
        return responseOK($result);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreFolderRequest $request)
    {
        DB::beginTransaction();
        try {
            if (auth()->user()->isStaff()) {
                $belongFolderOffice = $this->folderRepository->getFolderOfficeOfStaff();
                $foldersInOffice = $this->folderRepository->getFoldersByStoreId(auth()->user()->store_id)
                    ->pluck('id')
                    ->toArray();
                $foldersOwned = $this->folderRepository->getFoldersOwned();
                $folderParentCanCreateFolder = array_merge($foldersInOffice, $foldersOwned);
                array_push($folderParentCanCreateFolder, $belongFolderOffice->id);

                if (!in_array($request->parent_id, $folderParentCanCreateFolder)) {
                    return responseError(403, __('message.unauthorized'));
                }
            }
            $data = $request->only([
                'parent_id',
                'name',
            ]);
            if (!empty($request->parent_id)) {
                $folderParent = $this->folderRepository->findOrFail($request->parent_id);
                $data['branch_id'] = $folderParent->branch_id;
                $data['division_id'] = $folderParent->division_id;
                $data['office_id'] = $folderParent->office_id;
                $data['store_id'] = $folderParent->store_id;
            }
            $data['owner_id'] = auth('api')->user()->id;
            $folder = $this->folderRepository->create($data);
            DB::commit();
            return responseCreated(new FolderResource($folder));
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $folder = $this->folderRepository->show($id);
        return responseOK($folder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update($id, UpdateFolderRequest $request)
    {
        DB::beginTransaction();
        try {
            $folder = $this->folderRepository->findOrFail($id);
            if (auth()->user()->isStaff()) {
                $belongFolderOffice = $this->folderRepository->getFolderOfficeOfStaff();
                if ($belongFolderOffice && $folder->parent_id != $belongFolderOffice->id) {
                    return responseError(403, __('message.unauthorized'));
                }
            }
            $this->folderRepository->update($id, $request->only(['name']));
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
            $folder = $this->folderRepository->findOrFailFolder($id);
            if (auth()->user()->isStaff() && !$this->folderRepository->isOwner($folder)) {
                return responseError(403, __('message.unauthorized'));
            }
            $isHasDocument = $this->canDeleteFolder($folder);
            if ($folder->is_system == Folder::IS_SYSTEM || !$isHasDocument) {
                return responseError(403, __('message.delete_folder_failure'));
            }
            $this->folderRepository->delete($id);
            $folder->children()->delete();
            DB::commit();
            return responseUpdatedOrDeleted();
        } catch (\Exception $exception) {
            DB::rollBack();
            return responseError(500, $exception->getMessage());
        }
    }

    private function canDeleteFolder($folder)
    {
        if (!$folder->documents->isEmpty()) {
            return false;
        }
        foreach ($folder->children as $child) {
            if (!$child->documents->isEmpty() || !$this->canDeleteFolder($child)) {
                return false;
            }
        }
        return true;
    }

    public function getTreeFolder()
    {
        return responseOK(new TreeFolderCollection($this->folderRepository->getTreeFolder()));
    }

    public function getListWithBranch($officeId)
    {
        return responseOK(new FolderChildrenCollection($this->folderRepository->getListWithBranchId($officeId)));
    }
}
