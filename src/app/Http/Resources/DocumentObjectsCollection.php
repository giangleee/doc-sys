<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentObjectsCollection extends ResourceCollection
{
    public $collects = DocumentObjectsCollection::class;

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