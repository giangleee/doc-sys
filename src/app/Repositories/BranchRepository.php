<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchRepository extends BaseRepository
{
    public function getModel()
    {
        return Branch::class;
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
}
