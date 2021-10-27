<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewUserRegister extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $userInfo;
    protected $password;

    /**
     * Create a new message instance.
     */
    public function __construct($userInfo, $password)
    {
        $this->userInfo = $userInfo;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject(__('mail.user_registered.subject'))
            ->view('emails.user_register')
            ->with([
                'fullName' => $this->userInfo['full_name'],
                'userName' => $this->userInfo['user_name'],
                'employeeId' => $this->userInfo['employee_id'],
                'passwordDefault' => $this->password,
                'urlLogin' => config('app.url') . '/login'
            ]);
    }
}
