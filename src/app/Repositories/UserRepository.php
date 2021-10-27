<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository
{

    public function getModel()
    {
        return User::class;
    }

    public function getList(Request $request)
    {
        $model = $this->model->query();
        if ($request->employee_id) {
            $model->where('employee_id', 'like', '%' . $request->employee_id . '%');
        }
        if ($request->name) {
            $model->where('name', 'like', '%' . $request->name . '%');
        }
        if ($request->email) {
            $model->where('email', 'like', '%' . $request->email . '%');
        }
        if (!empty($request->positions)) {
            $model->whereIn('position_id', explode(',', $request->positions));
        }
        if ($request->roles) {
            $model->whereIn('role_id', explode(',', $request->roles));
        }
        $model->where(function ($q) use ($request) {
            if (!empty($request->branches)) {
                $q->whereIn('branch_id', explode(',', $request->branches));
            }
            if (!empty($request->divisions)) {
                $q->orWhereIn('division_id', explode(',', $request->divisions));
            }
            if (!empty($request->offices)) {
                $q->orWhereIn('office_id', explode(',', $request->offices));
            }
            if (!empty($request->stores)) {
                $q->orWhereIn('store_id', explode(',', $request->stores));
            }
        });
        $model->whereHas('role', function ($query) {
            $query->where('code', '<>', Role::SYSTEM_ADMIN);
        });
        return $model->orderBy('id', 'DESC')->paginate($request->limit ?? 9999999);
    }

    public function findByEmployeeId($employeeId)
    {
        return $this->model->where('employee_id', $employeeId)->first();
    }

    public function deletes($ids)
    {
        if (empty($ids)) {
            throw new \Exception(__('message.nothing_to_delete'), 404);
        }
        return $this->model->whereIn('id', $ids)->delete();
    }

//    public function findByEmployeeIds($employeeIds)
//    {
//        return $this->model
//            ->join('profiles', 'profiles.user_id', "=", 'users.id')
//            ->whereIn('users.employee_id', $employeeIds)
//            ->select([
//                'users.employee_id as employee_id',
//                DB::raw('CONCAT(users.id, "_", users.role_id, "_", profiles.id) as userId_role_profileId')
//            ])
//            ->pluck('userId_role_profileId', 'employee_id')
//            ->toArray();
//    }

    public function findByArrayEmployeeIds($employeeIds)
    {
        return $this->model->whereIn('employee_id', $employeeIds)->get();
    }

    public function getEmployeeID()
    {
        return $this->model->withTrashed()->get()->pluck('id', 'employee_id')->toArray();
    }

    public function deleteUserImport($employeeIDs, $roleSuperAdmin)
    {
        return $this->model->whereNotIn('employee_id', $employeeIDs)
            ->where('role_id', '!=', $roleSuperAdmin)
            ->delete();
    }

    public function getPositionOfStaffByOfficeId($officeId, $positionBelongRoleStaff)
    {
        return $this->model->where('office_id', $officeId)
            ->whereHas('role', function ($query) {
                $query->where('code', Role::STAFF);
            })
            ->whereIn('position_id', $positionBelongRoleStaff)
            ->pluck('position_id')
            ->toArray();
    }

    public function getAllWithOutRelation()
    {
        return $this->model->whereHas('role', function ($query) {
            $query->where('code', '<>', Role::SYSTEM_ADMIN);
        })->paginate(9999999);
    }

    public function getUserSystemAdmin()
    {
        return $this->model->where('role_id', function ($query) {
            $query->select('id')
                ->from(with(new Role)->getTable())
                ->where('code', Role::SYSTEM_ADMIN);
        })->pluck('employee_id')->toArray();
    }

    public function getUserSoftDelete($code)
    {
        return $this->model->onlyTrashed()->where('employee_id', $code)->first();
    }

    public function deleteAllWithOutSuperuser()
    {
        return $this->model->whereNotIn('role_id', function ($query) {
            $query->select('id')
                ->from(with(new Role)->getTable())
                ->where('code', Role::SYSTEM_ADMIN);
        })->delete();
    }

    public function restoreByID($ids)
    {
        return $this->model->withTrashed()
            ->whereIn('id', $ids)
            ->restore();
    }

    public function getUserWithTrash($code)
    {
        return $this->model->withTrashed()->where('employee_id', $code)->first();
    }
}
