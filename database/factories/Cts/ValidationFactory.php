<?php

use App\Models\Cts\Member;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\Validation::class, function (Faker $faker) {
    return [
        'position_id' => factory(\App\Models\Cts\ValidationPosition::class)->create()->id,
        'member_id' => factory(Member::class)->create()->id,
        'awarded_by' => factory(Member::class)->create()->id,
        'awarded_date' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
    ];
});
