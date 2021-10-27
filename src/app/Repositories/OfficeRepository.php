<?php


namespace App\Repositories;

use App\Helper\Constant;
use App\Models\Office;
use Illuminate\Http\Request;

class OfficeRepository extends BaseRepository
{
    public function getModel()
    {
        return Office::class;
    }

    public function getList(Request $request)
    {
        return $this->model->paginate($request->limit ? $request->limit : 9999999);
    }

    public function findByCode($code)
    {
        return $this->model->where('code', $code)->first();
    }

    public function findByArrayCodes($codes)
    {
        return $this->model->whereIn('code', $codes)->get();
    }

    public function getCode()
    {
        return $this->model->get()->pluck('id', 'code')->toArray();
    }

    public function getSupportOffice()
    {
        return $this->model->where('code', Constant::SUPPORT_OFFICE_CODE)->first();
    }

    public function getInfoOfficeByCode()
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
