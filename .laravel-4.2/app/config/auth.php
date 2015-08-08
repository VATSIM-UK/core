<?php

return array(

	"multi" => array(
            "user" => array(
                "driver" => "eloquent",
                "model" => "\Models\Mship\Account",
            ),
            "admin" => array(
                "driver" => "eloquent",
                "model" => "\Models\Mship\Account",
            )
        ),

	'reminder' => array(

		'email' => 'emails.auth.reminder',

		'table' => 'password_reminders',

		'expire' => 60,

	),

);
