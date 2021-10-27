<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class FreeDocumentCollection extends ResourceCollection
{
    public $collects = FreeDocumentResource::class;

    public function toArray($request)
    {
        return [
            'documents' => $this->collection
        ];
    }
}
