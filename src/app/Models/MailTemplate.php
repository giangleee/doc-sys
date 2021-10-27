<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailTemplate extends Model
{
    use SoftDeletes;
    const IS_SYSTEM = 1;
    const IS_NOT_SYSTEM = 0;
    const B2B_BEFORE_1_MONTH = '000001';
    const B2B_EXPIRATION_DATE = '000002';
    const B2B_AFTER_1_MONTH = '000003';
    const B2B_BEFORE_4_MONTH = '000004';
    const B2C_BEFORE_1_MONTH = '000005';
    const B2C_EXPIRATION_DATE = '000006';
    const B2C_AFTER_1_MONTH = '000007';
    const B2C_MISSING_DOCUMENT = '000008';
    const IMPORT_H2 = '000009';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'subject', 'body', 'user_created', 'user_updated'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mail_templates';

    public function userCreated()
    {
        return $this->belongsTo('App\Models\User', 'user_created');
    }

    public function userUpdated()
    {
        return $this->belongsTo('App\Models\User', 'user_updated');
    }

    public function mailDocuments()
    {
        return $this->hasMany('App\Models\MailDocument');
    }
}
