<?php


namespace App\Repositories;


use App\Models\Position;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PositionRepository extends BaseRepository
{
    public function getModel()
    {
        return Position::class;
    }

    public function getList(Request $request)
    {
        return $this->model->whereNull('deleted_at')->paginate($request->limit ? $request->limit : 9999999);
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
        return $this->model->select(['code', DB::raw('CONCAT(id, "__", role_id) as info_id')])
            ->get()
            ->pluck('info_id', 'code')
            ->toArray();
    }

    public function getAllPositionStaff()
    {
        return $this->model->where('role_id', function ($query) {
            $query->select('id')
                ->from(with(new Role)->getTable())
                ->where('code', Role::STAFF);
        })->get();
    }
}
