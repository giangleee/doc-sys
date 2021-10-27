<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class BasicUserCollection extends ResourceCollection
{
    public $collects = BasicUserResource::class;

    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
            'per_page' => $this->perPage(),
            'users' => $this->collection,
        ];
    }
}
