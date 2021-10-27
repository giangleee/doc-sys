<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'office_id' => $this->office_id,
            'office_name' => $this->office->name ?? null,
            'name' => $this->name,
            'email' => $this->email,
            'is_system' => $this->is_system
        ];
    }
}
