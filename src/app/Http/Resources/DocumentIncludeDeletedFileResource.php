<?php


namespace App\Http\Resources;


use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentIncludeDeletedFileResource extends JsonResource
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
            'service_user' => $this->serviceUser,
//            'partner_name' => $this->partner_name,
            'name' => $this->name,
            'tags' => $this->tags,
//            'attributes' => $this->attributes,
            'files' => $this->files()->onlyTrashed()->get(),
            'document_permission' => $this->documentPermission,
            'file_set_permission' => !empty($this->serviceUser) ? $this->serviceUser->fileSetPermission : null,
//            "mail_document" => $this->mailDocument,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
            'deleted_at' => !empty($this->deleted_at) ? $this->deleted_at->format(Constant::FORMAT_DATETIME) : null,
        ];
    }
}
