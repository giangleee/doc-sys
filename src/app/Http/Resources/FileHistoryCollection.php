<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class FileHistoryCollection extends ResourceCollection
{
    public $collects = FileHistoryResource::class;

    public function toArray($request)
    {
        return [
            'file_histories' => $this->collection,
        ];
    }
}
