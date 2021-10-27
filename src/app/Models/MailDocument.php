<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MailDocument extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'document_id',
        'mail_template_id',
        'to',
        'cc',
        'bcc',
        'type',
        'is_repeated',
        'repeat_unit',
        'repeat_value',
        'send_at',
        'last_send_at'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mail_document';

    const SEND_TYPE_AUTO = 1;
    const SEND_TYPE_MANUAL = 2;
    const NOT_REPEAT = 0;
    const REPEAT = 1;
    const REPEAT_WEEKLY = 1;
    const REPEAT_MONTHLY = 2;
    const REPEAT_YEARLY = 3;

    public function document()
    {
        return $this->belongsTo('App\Models\Document');
    }

    public function documentAlert()
    {
        return $this->belongsTo('App\Models\Document', 'document_id')
            ->with('documentObject', 'basicStore', 'serviceUser');
    }
    public function mailTemplate()
    {
        return $this->belongsTo('App\Models\MailTemplate');
    }
}
