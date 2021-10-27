<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class FolderChildrenResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this['id'],
            'branch_id' => $this['branch_id'],
            'division_id' => $this['division_id'],
            'childrens_count' => isset($this['childrens_count']) ? $this['childrens_count'] : 0,
            'owner_id' => $this['owner_id'],
            'parent_id' => $this['parent_id'],
            'name' => $this['name'],
            'disabled' => isset($this['disabled']) ? $this['disabled'] : false,
            'office_id' => $this['office_id'],
            'document_type_id' => $this['document_type_id'],
            'service_user_id' => $this['service_user_id'],
            'is_common' => $this['is_common'],
            'store_id' => $this['store_id'],
        ];
    }
}
