<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Office extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'division_id', 'name', 'code', 'email', 'is_system', 'hiiragi_code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'offices';

    const IS_SYSTEM = 1;

    public function division()
    {
        return $this->belongsTo('App\Models\Division');
    }

    public function serviceUsers()
    {
        return $this->belongsToMany(
            'App\Models\ServiceUser',
            'file_set_office_permission',
            'office_id',
            'service_user_id'
        )->withTimestamps();
    }

    public function documents()
    {
        return $this->belongsToMany(
            'App\Models\Document',
            'document_office_permission',
            'office_id',
            'document_id'
        )->withTimestamps();
    }

    public function stores()
    {
        return $this->hasMany('App\Models\Store');
    }
}
