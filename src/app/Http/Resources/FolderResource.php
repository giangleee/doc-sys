<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'name' => $this->name,
            'children' => $this->children,
        ];
    }
}
