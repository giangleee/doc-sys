<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class FreeDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'owner_id' => $this->owner_id,
            'folder_id' => $this->folder_id,
            'document_type_id' => $this->document_type_id,
            'service_user_id' => $this->service_user_id,
            'partner_name' => $this->partner_name,
            'name' => $this->name,
            'children' => $this->files,
            'document_type' => $this->documentType,
//            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
//            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
