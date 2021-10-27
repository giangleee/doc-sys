<?php

namespace App\Repositories;

use App\Models\PasswordReset;

class PasswordResetRepository extends BaseRepository
{
    public function getModel()
    {
        return PasswordReset::class;
    }

    public function findByToken($token)
    {
        return $this->model->where('token', $token)->first();
    }
}
