<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileSetPermission extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'file_set_management_id', 'service_user_id', 'store_id', 'positions_id',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_set_permission';
}
