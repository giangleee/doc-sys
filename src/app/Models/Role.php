<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'description'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

    const SYSTEM_ADMIN = 1;
    const ADMIN = 2;
    const EXECUTIVE = 3;
    const STAFF = 4;

    public function positions()
    {
        return $this->hasMany('App\Models\Position');
    }
}
