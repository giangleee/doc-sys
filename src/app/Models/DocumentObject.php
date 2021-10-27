<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentObject extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'document_objects';

    const IMPORTANT_MATTER_EXPLANATION = "000005";
    const INSURANCE_CERTIFICATE = "000006";
    const CERTIFICATE_SUPPORT = "000007";
    const CARE_PLAN = "000008";
    const HOME_PLAN = "000009";
    const CERTIFICATE_DISABILITY = "000012";
    const CARE_CARD = "0000014";
    const CERTIFICATE_OF_ELIGIBILITY = "000015";
    const CLIENT_SHEET = "000011";
    const CERTIFICATION = "000017";

    const ARR_HOME_CARE_AND_HOME_NURSING = [
        '000002', '000003', '000006', '000007', '000012', '000014', '000015'
    ];

    const ARR_WELFARE_CENTER = [
        '000002', '000003', '000006', '000048', '000012', '000014', '000015'
    ];

    /** document object is common */
    const COMMON_OBJECT = [
        DocumentType::HOME_CARE => self::ARR_HOME_CARE_AND_HOME_NURSING,
        DocumentType::HOME_NURSING => self::ARR_HOME_CARE_AND_HOME_NURSING,
        DocumentType::WELFARE_CENTER => self::ARR_WELFARE_CENTER
    ];
    public function documentTypes()
    {
        return $this->belongsToMany(
            'App\Models\DocumentType',
            'document_type_object',
            'document_object_id',
            'document_type_id'
        )->withTimestamps();
    }
}
