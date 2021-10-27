<?php

return [
    'reset_password' => [
        'subject' => 'Reset Password Notification',
        'line_intro' => 'You are receiving this email because we received a password reset request for your account.',
        'action' => 'Reset Password',
        'line_ignore' => 'If you did not request a password reset, no further action is required.'
    ],

    'user_registered' => [
        'subject' => 'Registration Notification',
        'line_intro' => 'Welcome :name,',
        'info_id' => 'Login using your ID: :id',
        'info_password' => 'Password: :password',
        'action' => 'Login',
    ]

];
