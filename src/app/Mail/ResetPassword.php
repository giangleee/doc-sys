<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $token;
    protected $fullName;

    /**
     * Create a new notification instance.
     */
    public function __construct($fullName, $token)
    {
        $this->fullName = $fullName;
        $this->token = $token;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject(__('mail.reset_password.subject'))
            ->view('emails.reset_password')
            ->with([
                'fullName' => $this->fullName,
                'url' => config('app.url') . '/reset-password/' . $this->token
            ]);
    }
}
