<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
//            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
//            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
