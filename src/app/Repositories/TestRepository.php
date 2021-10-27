<?php

namespace App\Repositories;

use App\Models\Test;

class TestRepository extends BaseRepository
{

    protected $msgNotFound = 'Test not found';

    public function getModel()
    {
        return Test::class;
    }
}
