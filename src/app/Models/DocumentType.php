<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'pattern_type', 'type', 'sort'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_types';

    const IS_SYSTEM = 1;
    const IS_NOT_SYSTEM = 0;
    const B2B = 1;
    const B2C = 2;
    const IS_TEMPLATE = 1;
    const IS_NOT_TEMPLATE = 2;
    const HOME_CARE = "0001";
    const HOME_NURSING = "0002";
    const CONTRACT = "0003";
    const NOTICE = "0004";
    const TEMPLATE = "0005";
    const RULE = "0006";
    const OTHER = "0007";
    const SERVICED_ELDERLY_HOUSING = "0008";
    const WELFARE_CENTER = "0009";
    const HOUSING_SUPPORT = "0010";
    const CHANGE_REGISTATION = "0011";
    const SIGN_UP_FOR_SUPPORT_PROJECTS = "0012";
    const DOCUMENT_COMMON = [
        self::HOME_CARE => DocumentObject::ARR_HOME_CARE_AND_HOME_NURSING,
        self::HOME_NURSING => DocumentObject::ARR_HOME_CARE_AND_HOME_NURSING,
        self::WELFARE_CENTER => DocumentObject::ARR_WELFARE_CENTER
    ];

    public function attributes()
    {
        return $this->belongsToMany(
            'App\Models\Attribute',
            'document_type_attribute',
            'document_type_id',
            'attribute_id'
        )->withTimestamps();
    }

    public function objects()
    {
        return $this->belongsToMany(
            'App\Models\DocumentObject',
            'document_type_object',
            'document_type_id',
            'document_object_id'
        )->withTimestamps();
    }
}
