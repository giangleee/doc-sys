<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'type', 'value',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attributes';

    const IS_INPUT = 1;
    const IS_SELECT= 2;
    const IS_MULTI_SELECT = 3;
    const IS_CHECKBOX = 4;
    const IS_RADIO = 5;
    const IS_DATE = 6;
    const IS_TEXTAREA = 7;
    const IS_DATETIME_RANGE = 8;
    const EXECUTED = '000001';
    const EXECUTION_DATE = '000002';
    const NOTE = '000003';
    const FORM_PERIOD = '000004';
    const SUPPLY_PERIOD = '000005';
    const VALIDITY_PERIOD = '000006';
    const STORAGE_PERIOD = '000007';

    public function getValueAttribute($value)
    {
        return json_decode($value);
    }
}
