<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Division extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id', 'name', 'code', 'hiiragi_code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'divisions';

    public function branch()
    {
        return $this->belongsTo('App\Models\Branch');
    }

    public function offices()
    {
        return $this->hasMany('App\Models\Office');
    }

    public function stores()
    {
        return $this->hasManyThrough('App\Models\Store', 'App\Models\Office');
    }
}
