<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class ListSearchServiceUserCollection extends ResourceCollection
{
    public $collects = ListSearchServiceUserResource::class;

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
