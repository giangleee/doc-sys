<?php


namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends BaseRepository
{
    protected $msgNotFound = 'Not found';

    public function getModel()
    {
        return Role::class;
    }

    public function getAll($request = [])
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

    public function getAllRole()
    {
        return $this->model->get()->pluck('id', 'code')->toArray();
    }

    public function isStaff($roleId = '')
    {
        $role = $this->find(!empty($roleId) ? $roleId : auth()->user()->role_id);
        return $role->code == Role::STAFF;
    }

    public function getRoleIsStaff()
    {
        return $this->model->where('code', Role::STAFF)->first();
    }
}
