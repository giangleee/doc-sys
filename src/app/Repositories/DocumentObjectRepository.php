<?php


namespace App\Repositories;

use App\Models\DocumentObject;

class DocumentObjectRepository extends BaseRepository
{
    public function getModel()
    {
        return DocumentObject::class;
    }

    public function getList()
    {
        return $this->model->get();
    }

    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getObjectByCodes($codes)
    {
        return $this->model->whereIn('code', $codes)->get();
    }

    public function getObjectByName($name)
    {
        return $this->model->where('name', $name)->first();
    }

}
