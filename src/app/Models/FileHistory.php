<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'file_id', 'file_format', 'original_name','url', 'size', 'version', 'action'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file_history';

    const  ACTION_INSERT = 1;
    const  ACTION_UPDATE = 2;
    const  ACTION_DELETE = 3;
    const  ACTION_REVERT = 4;
}
