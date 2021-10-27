<?php

namespace App\Repositories;

use App\Models\Profile;
use App\Models\User;
use App\Models\Role;

class ProfileRepository extends BaseRepository
{
    public function getModel()
    {
        return Profile::class;
    }

    public function findByUserId($userId)
    {
        return $this->model->where('user_id', $userId)->firstOrFail();
    }

    public function getProfileUser($userId)
    {
        return $this->model->where('user_id', $userId)->first();
    }

    public function getAllProfile()
    {
        return $this->model->withTrashed()->get()->pluck('id', 'user_id')->toArray();
    }

    public function deleteByUser($employeeIDs, $roleSuperAdmin)
    {
        return $this->model->whereIn('user_id', function ($query) use ($employeeIDs, $roleSuperAdmin) {
            $query->select('id')
                ->from(with(new User)->getTable())
                ->whereNotIn('employee_id', $employeeIDs)
                ->where('role_id', '!=', $roleSuperAdmin);
        })->delete();
    }

    public function getProfileWithTrash($userID)
    {
        return $this->model->withTrashed()->where('user_id', $userID)->first();
    }

    public function deleteProfileWithOutSuperuser()
    {
        return $this->model->whereNotIn('user_id', function ($query) {
            $query->select('id')
                ->from(with(new User)->getTable())
                ->where('role_id', function ($q) {
                    $q->select('id')
                        ->from(with(new Role)->getTable())
                        ->where('code', Role::SYSTEM_ADMIN);
                });
        })->delete();
    }

    public function restoreByUserID($userIDs)
    {
        return $this->model->withTrashed()
            ->whereIn('user_id', $userIDs)
            ->restore();
    }
}
