<?php

use App\Models\Cts\Member;
use App\Models\Cts\Position;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\PositionValidation::class, function (Faker $faker) {
    return [
        'member_id' => factory(Member::class)->create()->id,
        'position_id' => Position::factory()->create()->id,
        'status' => rand(1, 5),
        'changed_by' => 1111111,
        'date_changed' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
    ];
});
