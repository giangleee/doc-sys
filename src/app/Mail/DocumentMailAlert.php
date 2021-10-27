<?php

namespace App\Mail;

use App\Helper\Constant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentMailAlert extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $mailSubject;
    protected $mailBody;

    /**
     * Create a new notification instance.
     */
    public function __construct($mailSubject, $mailBody)
    {
        $this->mailSubject = $mailSubject;
        $this->mailBody = $mailBody;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->html($this->mailBody)->subject($this->mailSubject);
    }
}
