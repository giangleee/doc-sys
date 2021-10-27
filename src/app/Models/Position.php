<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'role_id', 'name', 'code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'positions';

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }
}
