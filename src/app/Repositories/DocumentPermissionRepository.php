<?php


namespace App\Repositories;


use App\Models\DocumentPermission;
use Illuminate\Support\Facades\DB;

class DocumentPermissionRepository extends BaseRepository
{
    public function getModel()
    {
        return DocumentPermission::class;
    }

    public function getDocumentPermission($documentId)
    {
        return $this->model->where('document_id', $documentId)
            ->where('store_id', auth()->user()->store_id)
            ->first();
    }

    public function getDocumentsIdHasPermission($userInfo)
    {
        return $this->model
            ->where('store_id', $userInfo->store_id)
            ->where(DB::raw('CONCAT(",", positions_id, ",")'), 'like', '%,' . $userInfo->position_id . ',%')
            ->pluck('document_id')
            ->toArray();
    }

    public function getDocumentsIdNoPermission($userInfo, $documentsIdHasPermission)
    {
        return $this->model
            ->where(function ($query) use ($userInfo, $documentsIdHasPermission) {
                $query->where('store_id', '<>', $userInfo->store_id)
                    ->whereNotIn('document_id', $documentsIdHasPermission);
            })
            ->orWhere(function ($query) use ($userInfo) {
                $query->where('store_id', $userInfo->store_id)
                    ->where(
                        DB::raw('CONCAT(",", positions_id, ",")'),
                        'not like',
                        '%,' . $userInfo->position_id . ',%'
                    );
            })
            ->pluck('document_id')
            ->toArray();
    }

    public function deleteByDocumentIds($documentIDs, $office)
    {
        return $this->model->whereIn('document_id', $documentIDs)
            ->whereNotIn('store_id', $office)
            ->delete();
    }
}
