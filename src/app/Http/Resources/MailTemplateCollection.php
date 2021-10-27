<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class MailTemplateCollection extends ResourceCollection
{
    public $collects = MailTemplateResource::class;

    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
            'per_page' => $this->perPage(),
            'mail_templates' => $this->collection,
        ];
    }
}
