<?php


namespace App\Repositories;


use App\Models\DocumentType;

class DocumentTypeRepository extends BaseRepository
{
    public function getModel()
    {
        return DocumentType::class;
    }

    public function getList()
    {
        return $this->model->orderBy('sort', 'ASC')->get();
    }

    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getInfoGroupByCode()
    {
        $result = $this->getList();
        $data = [];
        foreach ($result as $item) {
            $data[$item['code']] = [
                'id' => $item['id'],
                'name' => $item['name']
            ];
        }

        return $data;
    }

    public function getDocumentTypeWithID($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getDocumentTypeWithIDS($arrayIds)
    {
        return $this->model->whereIn('id', $arrayIds)->get();
    }
}
