<?php


namespace App\Repositories;


use App\Models\Document;
use App\Models\FileSetPermission;
use App\Models\ServiceUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Services\FileService;
use App\Jobs\ImportServiceUserJob;
use Carbon\Carbon;
use App\Helper\Constant;

class ServiceUserRepository extends BaseRepository
{
    public function getModel()
    {
        return ServiceUser::class;
    }

    public function getList(Request $request)
    {
        $limit = $request->limit ?? 9999999;
        if (auth()->user()->isStaff()) {
            return $this->getServiceUserAvailableForStaff($limit);
        }
        return $this->model->orderBy('id', 'DESC')->paginate($limit);
    }

    public function deletes($ids)
    {
        if (empty($ids)) {
            throw new \Exception(__('message.nothing_to_delete'), 404);
        }
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function setFileSetAccess($serviceUser, $params)
    {
        //init
        $filesetManageRepo = new FileSetManagementRepository();
        $documentRepository = new DocumentRepository();
        $positionRepository = new PositionRepository();
        $filesetPermissionRepo = new FileSetPermissionRepository();
        $documentPermissionRepo = new DocumentPermissionRepository();

        $documentIdsInFileset = $documentRepository->getDocumentsInFileSet(
            $serviceUser->id,
            $params['document_type_id'],
            $params['store_id']
        )->pluck('id')->toArray();
        $positionIDs = $positionRepository->getAllPositionStaff()->pluck('id')->toArray();
        $filesetManage = $filesetManageRepo->filterFirst([
            'service_user_id' => $serviceUser->id,
            'store_id' => $params['store_id'],
            'document_type_id' => $params['document_type_id']
        ]);

        if (!$filesetManage) {
            //TODO throw exception
        }

        //build data fileset permission
        $dataFilesetPermission = [];
        $storeFilesetPermission = [];
        if (isset($params['data'])) {
            foreach ($params['data'] as $roleSetting) {
                $storeFilesetPermission[] = $roleSetting['store'];
                $dataFilesetPermission[] = [
                    'file_set_management_id' => $filesetManage->id,
                    'store_id' => $roleSetting['store'],
                    'positions_id' => empty($roleSetting['positions']) ? implode(',', $positionIDs) : implode(',', $roleSetting['positions'])
                ];
            }
        }

        //delete fielset permission
        if (!empty($storeFilesetPermission)) {
            $filesetPermissionRepo->deleteByOffice($storeFilesetPermission, $filesetManage->id);
            if (!empty($documentIdsInFileset)) {
                $documentPermissionRepo->deleteByDocumentIds(
                    $documentIdsInFileset,
                    $storeFilesetPermission
                );
            }
        }

        //insert or update fileset permission
        foreach ($dataFilesetPermission as $item) {
            $filesetPermissionRepo->updateOrCreateData(
                [
                    'file_set_management_id' => $filesetManage->id,
                    'store_id' => $item['store_id']
                ],
                $item
            );
            foreach ($documentIdsInFileset as $documentId) {
                $documentPermissionRepo->updateOrCreateData(
                    [
                        'document_id' => $documentId,
                        'store_id' => $item['store_id']
                    ],
                    [
                        'document_id' => $documentId,
                        'store_id' => $item['store_id'],
                        'positions_id' => $item['positions_id']
                    ]
                );
            }
        }

        return true;
    }

    public function getServiceUsersByIds($arrayIds, $withTrashed = false)
    {
        if ($withTrashed) {
            return $this->model->whereIn('id', $arrayIds)->withTrashed()->get();
        }
        return $this->model->whereIn('id', $arrayIds)->get();
    }

    public function getServiceUserAvailableForStaff($limit = 9999999)
    {
        $currentUser = auth()->user();
        $fileSetManagementRepo = new FileSetManagementRepository();
        $filesetIdsHasPermission = $fileSetManagementRepo->getPermissionByUser($currentUser);

        return $this->model->whereIn('id', $filesetIdsHasPermission)
            ->orWhereNotIn('id', function ($query) use ($filesetIdsHasPermission) {
                $query->select('service_user_id')
                    ->from(with(new Document())->getTable())
                    ->whereNotNull('service_user_id')
                    ->whereNotIn('service_user_id', $filesetIdsHasPermission);
            })
            ->orderBy('id', 'DESC')
            ->paginate($limit);
    }

    public function getServiceUserAndOfficeByIds($ids)
    {
        return $this->model->whereIn('id', $ids)->with('office')->get();
    }
    public function getServiceUserHasDocument()
    {
        return $this->model->with('serviceUserHasDocument')->whereHas('serviceUserHasDocument')->get();
    }

    public function search($param)
    {
        $user = auth()->user();
        $column = $param['column'] ?? 'id';
        $sort = $param['sort'] ?? 'DESC';
        $serviceUser = $this->model->orderBy($column, $sort);
        //get user service with permission
        if ($user->isStaff()) {
            $fileSetManagementRepo = new FileSetManagementRepository();
            $filesetIdsHasPermission = $fileSetManagementRepo->getPermissionByUser($user);

            $serviceUser = $serviceUser
                ->whereIn('id', $filesetIdsHasPermission)
                ->orWhereNotIn('id', function ($query) use ($filesetIdsHasPermission) {
                    $query->select('service_user_id')
                        ->from(with(new Document())->getTable())
                        ->whereNotNull('service_user_id')
                        ->whereNotIn('service_user_id', $filesetIdsHasPermission);
                });

        }

        //get by text search
        if (isset($param['s']) && $param['s']) {
            return $serviceUser->where(function ($query) use ($param) {
                $query->where('code', 'LIKE', '%' . $param['s'] . '%')
                    ->orWhere('name', 'LIKE', '%' . $param['s'] . '%');
            })->paginate(10);
        }

        //get default
        return $serviceUser->paginate(10);
    }

    public function getServiceUserAlert()
    {
        return $this->model->whereIn('id', function ($query) {
            $query->select('service_user_id')
                ->from(with(new Document)->getTable())
                ->whereNotNull('service_user_id');
        })->with('office')->get();
    }

    public function import($request, $files)
    {
        //upload file to S3
        $folder = 'import/' . Carbon::now()->format('Y-m-d') . '/' . Carbon::now()->timestamp . '/';
        $fileService = new FileService();
        foreach ($request->file('files') as $item) {
            //change file name
            $filename = pathinfo($item->getClientOriginalName());
            $fileNameUpload = '';
            if ($files['service_user'] == $filename['basename']) {
                $fileNameUpload = 'service_' . auth()->user()->id . '_' . $filename['basename'];
            }
            if ($files['office'] == $filename['basename']) {
                $fileNameUpload = 'office_' . auth()->user()->id . '_' . $filename['basename'];
            }

            //upload file to s3
            $fileService->uploadFileChangeName(
                $folder,
                $item,
                $fileNameUpload
            );
        }

        return true;
    }

    public function getAllWithTrash()
    {
        return $this->model->withTrashed()->get()->pluck('id', 'code')->toArray();
    }

    public function deleteAll()
    {
        return $this->model->where('id', 'like', '%%')->delete();
    }

    public function restoreByCode($codes)
    {
        return $this->model->withTrashed()
            ->whereIn('code', $codes)
            ->restore();
    }

    public function getServiceUsersByCodes($arrayCodes, $withTrashed = false)
    {
        if ($withTrashed) {
            return $this->model->whereIn('code', $arrayCodes)->withTrashed()->get();
        }
        return $this->model->whereIn('code', $arrayCodes)->get();
    }
}
