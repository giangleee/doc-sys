<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class OfficeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'division_id' => $this->division_id,
            'division_name' => $this->division->name ? $this->division->name : null,
            'name' => $this->name,
            'email' => $this->email,
            'is_system' => $this->is_system,
//            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
//            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
