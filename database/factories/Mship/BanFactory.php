<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Mship\Ban\Reason::class, function (Faker $faker) {
    return [
        'name' => $faker->words(2, true),
        'reason_text' => $faker->paragraph,
        'period_amount' => $faker->randomDigitNot(0),
        'period_unit' => $faker->randomElement(['M', 'H', 'D']),
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->define(\App\Models\Mship\Account\Ban::class, function (Faker $faker) {
    return [
        'account_id' => function () {
            return \App\Models\Mship\Account::factory()->create()->id;
        },
        'banned_by' => function () {
            return \App\Models\Mship\Account::factory()->create()->id;
        },
        'type' => \App\Models\Mship\Account\Ban::TYPE_LOCAL,
        'reason_id' => function () {
            return factory(\App\Models\Mship\Ban\Reason::class)->create()->id;
        },
        'reason_extra' => $faker->paragraph,
        'period_start' => \Carbon\Carbon::now()->subDay(),
        'period_finish' => \Carbon\Carbon::now()->addDays($faker->randomDigitNotNull),
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
