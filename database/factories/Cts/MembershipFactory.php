<?php

use App\Models\Cts\Member;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\Membership::class, function (Faker $faker) {
    return [
        'rts_id' => 1,
        'member_id' => factory(Member::class)->create(),
        'type' => 'H',
        'rtsm' => 0,
        'rtsi' => 0,
        'hidden' => '0',
        'sequence' => 0,
        'other' => 0,
        'pending' => 0,
        'joined' => '2019-01-01',
        'confirmed' => null,
    ];
});
