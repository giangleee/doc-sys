<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'hiiragi_code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'branches';

    public function divisions()
    {
        return $this->hasMany('App\Models\Division');
    }
}
