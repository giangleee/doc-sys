<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
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
            'store' => $this->store,
            'owner' => $this->owner,
            'folder' => $this->folder,
            'document_type' => $this->documentType,
            'document_object' => $this->documentObject,
            'service_user' => $this->serviceUser,
            'partner_name' => $this->partner_name,
            'name' => $this->name,
            'tags' => $this->tags,
            'attributes' => $this->attributes,
            'files' => $this->files()->paginate(isset($request->limit) ? $request->limit : 10),
            'document_permission' => $this->documentPermission,
            'file_set_permission' => !empty($this->serviceUser)
                ? $this->serviceUser->fileSetManagementWithOffice($this->store_id, $this->document_type_id)
                    ->fileSetPermission
                : null,
            'mail_document' => $this->mailDocument,
            'version' => $this->version,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
