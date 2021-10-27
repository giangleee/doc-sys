<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Store extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'office_id', 'name', 'code', 'email', 'is_system', 'hiiragi_code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stores';

    const IS_SYSTEM = 1;

    public function office()
    {
        return $this->belongsTo('App\Models\Office');
    }
}
