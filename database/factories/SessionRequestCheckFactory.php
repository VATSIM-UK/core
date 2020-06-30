<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Training\SessionRequestCheck;
use Faker\Generator as Faker;

$factory->define(SessionRequestCheck::class, function (Faker $faker) {
    return [
       'rts_id' => 1,
       'account_id' => factory(\App\Models\Mship\Account::class),
       'stage' => 0,
    ];
});
