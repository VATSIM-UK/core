<?php

$factory->define(\Laravel\Passport\Client::class, function ($faker) {
    return [
        'name' => $faker->sentence(2),
        'secret' => $faker->sha256,
        'redirect' => "",
        'personal_access_client' => true,
        'password_client' => true,
        'revoked' => false,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});


$factory->define(\App\Models\Sso\Email::class, function ($faker) {
    return [
        'account_email_id' => function () {
            return factory(\App\Models\Mship\Account\Email::class)->create()->id;
        },
        'sso_account_id' => function () {
            return factory(\Laravel\Passport\Client::class)->create()->id;
        },
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
