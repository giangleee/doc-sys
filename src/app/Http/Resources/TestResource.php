<?php


namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\Constant;

class TestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
