<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Mship\Feedback\Form::class, function (Faker $faker) {
    return [
        'name' => $faker->words(2, true),
        'slug' => strtolower($faker->word),
        'contact_id' => $faker->numberBetween(1, 3),
        'enabled' => 1,
        'targeted' => 1,
        'public' => 1,
    ];
});

$factory->define(App\Models\Mship\Feedback\Question\Type::class, function (Faker $faker) {
    return [
        'name' => $faker->word,
        'code' => $faker->words(10, true),
    ];
});

$factory->define(App\Models\Mship\Feedback\Question::class, function (Faker $faker) {
    return [
        'type_id' => factory(App\Models\Mship\Feedback\Question\Type::class)->create(),
        'form_id' => factory(App\Models\Mship\Feedback\Form::class)->create(),
        'slug' => $faker->word,
        'question' => $faker->sentence,
        'required' => 0,
        'sequence' => 1,
        'permanent' => 1,
    ];
});
