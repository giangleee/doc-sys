<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class TreeFolderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'branch' => $this->branch,
            'division' => $this->division,
            'office' => $this->office,
            'store' => $this->store,
            'owner_id' => $this->owner_id,
            'owner_name' => $this->user->name,
            'parent_id' => $this->parent_id,
            'parent_name' => isset($this->parent->name) ? $this->parent->name : null,
            'name' => $this->name,
            'children' => count($this->children),
            'documents' => count($this->documents),
            'is_system' => $this->is_system,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
