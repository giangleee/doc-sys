<?php


namespace App\Repositories;

use App\Models\Store;
use Illuminate\Http\Request;

class StoreRepository extends BaseRepository
{
    public function getModel()
    {
        return Store::class;
    }

    public function getList(Request $request)
    {
        return $this->model->paginate($request->limit ?? 9999999);
    }

    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function getCode()
    {
        return $this->model->get()->pluck('id', 'code')->toArray();
    }

    public function getHiragicode($value = 'id')
    {
        return $this->model->whereNotNull('hiiragi_code')
            ->get()->pluck($value, 'hiiragi_code')->toArray();
    }

    public function getInfoStoreByCode()
    {
        $result = $this->model->get();
        $data = [];
        foreach ($result as $item) {
            $data[$item['code']] = [
                'id' => $item['id'],
                'name' => $item['name']
            ];
        }

        return $data;
    }
}
