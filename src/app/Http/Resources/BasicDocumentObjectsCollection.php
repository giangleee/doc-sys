<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BasicDocumentObjectsCollection extends ResourceCollection
{
    public $collects = BasicDocumentObjectsCollection::class;

    public function toArray($request)
    {
        return [
            // 'current_page' => $this->currentPage(),
            // 'last_page' => $this->lastPage(),
            // 'total' => $this->total(),
            // 'per_page' => $this->perPage(),
            'document_objects' => $this->collection,
        ];
    }
}