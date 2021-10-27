<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\Constant;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'branch' => $this->branch,
            'division' => $this->division,
            'office' => $this->office,
            'store' => $this->store,
            'position' => $this->position,
            "employee_id" => $this->employee_id,
            'name' => $this->name,
            'full_name' =>$this->profile->full_name ?? '',
            'katakana_name' => $this->profile->katakana_name ?? '',
            'avatar' => $this->profile->avatar ?? '',
            'email' => $this->email,
            'phone' => $this->profile->phone ?? '',
            'is_first_login' => $this->is_first_login,
            'status' => $this->status,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
