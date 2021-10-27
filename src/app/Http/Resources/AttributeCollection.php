<?php


namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class AttributeCollection extends ResourceCollection
{
    public $collects = AttributeResource::class;

    public function toArray($request)
    {
        return [
            'attributes' => $this->collection
        ];
    }
}
