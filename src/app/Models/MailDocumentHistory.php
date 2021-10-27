<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailDocumentHistory extends Model
{
    protected $fillable = [
        'mail_document_id',
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
    protected $table = 'mail_document_history';
}
