<?php

    use Faker\Generator as Faker;

    $factory->define(\App\Models\Mship\Ban\Reason::class, function (Faker $faker) {
        return [
            'name' => $faker->words(3, true),
            'reason_text' => $faker->paragraph,
            'period_amount' => $faker->randomDigit,
            'period_unit' => $faker->randomElement(['M', 'H', 'D']),
            'created_at' => $faker->dateTime(),
            'updated_at' => $faker->dateTime(),
        ];
    });

    $factory->define(\App\Models\Mship\Account\Ban::class, function (Faker $faker) {
        return [
            'account_id' => function () {
                return factory(\App\Models\Mship\Account::class)->create()->id;
            },
            'banned_by' => function () {
                return factory(\App\Models\Mship\Account::class)->create()->id;
            },
            'type' => \App\Models\Mship\Account\Ban::TYPE_LOCAL,
            'reason_id' => function () {
                return factory(\App\Models\Mship\Ban\Reason::class)->create()->id;
            },
            'reason_extra' => $faker->paragraph,
            'period_start' => \Carbon\Carbon::now(),
            'period_start' => \Carbon\Carbon::now()->addDays($faker->randomDigit),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ];
    });
