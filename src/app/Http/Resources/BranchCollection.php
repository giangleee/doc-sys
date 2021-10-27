<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class BranchCollection extends ResourceCollection
{
    public $collects = BranchResource::class;

    public function toArray($request)
    {
        return [
            'current_page' => $this->currentPage(),
            'last_page' => $this->lastPage(),
            'total' => $this->total(),
            'per_page' => $this->perPage(),
            'branches' => $this->collection,
        ];
    }
}
