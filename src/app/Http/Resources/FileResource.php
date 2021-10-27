<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'document' => $this->document,
            'file_format' => $this->file_format,
            'original_name' => $this->original_name,
            'url' => $this->url,
            'size' => $this->size,
            'version' => $this->version,
        ];
    }
}
