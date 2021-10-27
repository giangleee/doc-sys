<?php


namespace App\Http\Resources;


use App\Repositories\DocumentRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FolderCollection extends ResourceCollection
{
    public $collects = FolderResource::class;

    public function toArray($request)
    {
        return [
            'folders' => $this->collection,
        ];
    }
}
