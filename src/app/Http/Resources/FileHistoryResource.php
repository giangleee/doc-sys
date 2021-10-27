<?php

namespace App\Http\Resources;

use App\Helper\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

class FileHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'file_id' => $this->file_id,
            'file_format' => $this->file_format,
            'original_name' => $this->original_name,
            'url' => $this->url,
            'size' => $this->size,
            'version' => $this->version,
            'action' => $this->action,
            'created_at' => $this->created_at->format(Constant::FORMAT_DATETIME),
            'updated_at' => $this->updated_at->format(Constant::FORMAT_DATETIME),
        ];
    }
}
