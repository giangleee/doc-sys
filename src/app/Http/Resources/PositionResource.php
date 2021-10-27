<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'role' => $this->role,
//            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
//            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
