<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    //
    protected $fillable = ['send_alert', 'exchange_deadline'];

    const SEND_ALERT_ON = 1;
    const SEND_ALERT_OFF = 0;
}
