<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Smartcars\Bid::class, function (Faker $faker) {
    return [
        'flight_id' => function () {
            return factory(\App\Models\Smartcars\Flight::class)->create()->id;
        },
        'account_id' => function () {
            return factory(\App\Models\Mship\Account::class)->create()->id;
        },
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
        'completed_at' => \Carbon\Carbon::now(),
    ];
});
