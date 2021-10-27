<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentTypeCollection extends ResourceCollection
{
    public $collects = DocumentTypeResource::class;

    public function toArray($request)
    {
        return [
        //    'current_page' => $this->currentPage(),
        //    'last_page' => $this->lastPage(),
        //    'total' => $this->total(),
        //    'per_page' => $this->perPage(),
            'document_types' => $this->collection,
        ];
    }
}
