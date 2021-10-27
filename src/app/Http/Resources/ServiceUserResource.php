<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceUserResource extends JsonResource
{

    public function toArray($request)
    {
        $limit = isset($request->limit) ? $request->limit : 9999999;
        $filesetManage = $this->fileSetManagementWithOffice($request->store_id, $request->document_type_id);
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'document_type' => !$filesetManage ? null : $filesetManage->documentType->name,
            'documents' => !empty($request->store_id)
                ? $filesetManage
                    ->documentsWithOffice($request->store_id, $request->document_type_id)->paginate($limit) : null,
            'permission' => !$filesetManage ? null : $filesetManage->fileSetPermission
//            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
//            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
