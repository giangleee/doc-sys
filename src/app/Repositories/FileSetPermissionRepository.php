<?php


namespace App\Repositories;


use App\Models\FileSetPermission;
use Illuminate\Support\Facades\DB;

class FileSetPermissionRepository extends BaseRepository
{
    public function getModel()
    {
        return FileSetPermission::class;
    }

    public function getFileSetPermission($fileSetManagementId)
    {
        return $this->model->where('file_set_management_id', $fileSetManagementId)->get();
    }

    public function getPermissionByUser($userInfo)
    {
        return $this->model->where('store_id', $userInfo->store_id)
            ->where(DB::raw('CONCAT(",", positions_id, ",")'), 'like', '%,' . $userInfo->position_id . ',%')
            ->pluck('service_user_id')
            ->toArray();
    }

    public function deleteByServiceUser($serviceUserId)
    {
        return $this->model->where('service_user_id', $serviceUserId)->delete();
    }

    public function deleteByOffice($storesId, $fileSetManagementId)
    {
        return $this->model->whereNotIn('store_id', $storesId)
            ->where('file_set_management_id', $fileSetManagementId)
            ->delete();
    }

    public function deleteByServiceUserWithoutManage($serviceUserId)
    {
        return $this->model->where('service_user_id', $serviceUserId)
            ->where('file_set_management_id', 0)
            ->delete();
    }
}
