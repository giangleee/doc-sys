<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class DocumentIncludeDeletedFileCollection extends ResourceCollection
{
    public $collects = DocumentIncludeDeletedFileResource::class;

    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
            'per_page' => $this->perPage(),
            'documents' => $this->collection,
        ];
    }
}
