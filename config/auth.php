<?php

return [

    'multi' => [
        'admin' => [
            'driver' => 'eloquent',
            'model' => '\Models\Mship\Account',
        ],
        'user' => [
            'driver' => 'eloquent',
            'model' => '\Models\Mship\Account',
        ]
    ],

    'password' => [
        'email' => 'emails.auth.reminder',
        'table' => 'password_reminders',
        'expire' => 60,
    ],

];
