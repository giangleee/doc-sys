<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceUserCollection extends ResourceCollection
{
    public $collects = ServiceUserResource::class;
    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
            'per_page' => $this->perPage(),
            'service_users' => $this->collection,
        ];
    }
}
