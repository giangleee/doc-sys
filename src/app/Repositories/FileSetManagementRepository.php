<?php

namespace App\Repositories;

use App\Helper\Constant;
use App\Models\FileSetManagement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FileSetManagementRepository extends BaseRepository
{
    public function getModel()
    {
        return FileSetManagement::class;
    }

    public function checkExitsFileSet($service_user_id, $document_type_id, $storeId)
    {
        $fileSet = $this->model->where(function ($q) use ($service_user_id, $document_type_id, $storeId) {
            $q->where('service_user_id', $service_user_id);
            $q->where('document_type_id', $document_type_id);
            $q->where('store_id', $storeId);
        })->first();
        return $fileSet;
    }

    public function getFileSetWithServiceUser()
    {
        return $this->model->with(['serviceUser', 'documentType', 'store'])
            ->leftJoin('documents', function ($join) {
                $join->on('documents.service_user_id', '=', 'file_set_management.service_user_id')
                    ->on('documents.store_id', '=', 'file_set_management.store_id')
                    ->on('documents.document_type_id', '=', 'file_set_management.document_type_id');
            })
            ->join('document_objects',
                'documents.document_object_id', '=', 'document_objects.id'
            )
            ->select(DB::raw(
                'file_set_management.*,
                documents.id as documents_id,
                documents.store_id as documents_store_id,
                documents.document_type_id as documents_type_id,
                documents.deleted_at as documents_deleted_at,
                document_objects.id as document_objects_id,
                document_objects.code as document_objects_code'
            ))
            ->get();
    }

    public function getPermissionByUser($userInfo)
    {
        return $this->model->where('store_id', $userInfo->store_id)
            ->whereHas('fileSetPermission', function ($q) use ($userInfo) {
                $q->where(DB::raw('CONCAT(",", positions_id, ",")'), 'like', '%,' . $userInfo->position_id . ',%');
            })
            ->pluck('service_user_id')
            ->toArray();
    }

    public function getFileSetIdsByServiceUser($serviceUserIds)
    {
        return $this->model->whereIn('service_user_id', $serviceUserIds)->pluck('id')->toArray();
    }

    public function filterWithTrashed($where)
    {
        return $this->model->where($where)->withTrashed()->first();
    }

    public function checkAnotherFileSetAvailable($serviceUserId, $documentTypeId, $storeId, $fileSetManagementSiblings)
    {
        $dateOld = Carbon::now()->subYears(Constant::YEAR_STOP_CONTRACT)->format('Y-m-d 00:00:00');
        return $this->model->where('service_user_id', $serviceUserId)
            ->where('id', '<>', $fileSetManagementSiblings)
            ->where('document_type_id', '<>', $documentTypeId)
            ->where('store_id', $storeId)
            ->where(function ($query) use ($dateOld) {
                $query->whereNull('contract_cancel_date')
                    ->orWhere('contract_cancel_date', '>', $dateOld);
            })
            ->first();
    }

    public function getAllFilesetInServiceUser($serviceUserId, $filesetID)
    {
        return $this->model->where('service_user_id', $serviceUserId)
            ->where('id', '<>', $filesetID)
            ->first();
    }
}
