<?php

namespace App\Repositories;

use App\Models\Attribute;

class AttributeRepository extends BaseRepository
{

    /**
     * @inheritDoc
     */
    public function getModel()
    {
        return Attribute::class;
    }

    public function getList()
    {
        return $this->model->get();
    }
}
