<?php

use App\Models\Cts\Member;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\Event::class, function (Faker $faker) {
    $from = $faker->time();

    return [
        'event' => $faker->sentence,
        'tagline' => 'event tagline',
        'date' => $faker->dateTimeInInterval('+1 YEAR')->format('Y-m-d'),
        'from' => $from,
        'to' => Carbon::createFromTimeString($from)->addHours(rand(1, 4))->toTimeString(),
        'add_by' => factory(Member::class)->create()->id,
        'text' => $faker->paragraph,
        'thread' => $faker->url,
    ];
});
