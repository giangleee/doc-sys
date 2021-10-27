<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends BaseModel
{
    use SoftDeletes;

    protected $fillable = ['name']; // fillable fields

    protected $table = 'test_table'; // table name


    // relationship
    public function actors()
    {
        return $this->belongsToMany(Actor::class, 'actor_settings', 'actor_tag_id', 'actor_id');
    }

    // example scope
    public function scopeTest($query)
    {
        return $query->where('test', 'test');
    }
}
