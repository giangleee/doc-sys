<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentObjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $result = [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
        ];
        if (in_array($this->code, Constant::IS_B2C)) {
            $result['objects'] = $this->objects;
        }
        return $result;
    }
}