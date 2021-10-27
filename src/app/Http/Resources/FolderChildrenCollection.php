<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class FolderChildrenCollection extends ResourceCollection
{
    public $collects = FolderChildrenResource::class;

    public function toArray($request)
    {
        return $this->collection;
    }
}
