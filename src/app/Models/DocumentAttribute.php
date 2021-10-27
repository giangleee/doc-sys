<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DocumentAttribute extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'document_id', 'attribute_id', 'value', 'start_date', 'end_date'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_attributes';

    public function setStartDateAttribute($value)
    {
        $this->attributes['start_date'] = Carbon::createFromTimestamp(strtotime($value))->format('Y-m-d');
    }

    public function setEndDateAttribute($value)
    {
        $this->attributes['end_date'] = Carbon::createFromTimestamp(strtotime($value))->format('Y-m-d');
    }
}
