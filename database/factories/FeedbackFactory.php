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
        'type_id' => factory(App\Models\Mship\Feedback\Question\Type::class)->create()->id,
        'form_id' => factory(App\Models\Mship\Feedback\Form::class)->create()->id,
        'slug' => $faker->word,
        'question' => $faker->sentence,
        'required' => 0,
        'sequence' => 1,
        'permanent' => 1,
    ];
});

$factory->define(App\Models\Mship\Feedback\Feedback::class, function (Faker $faker) {
    return [
        'form_id' => factory(App\Models\Mship\Feedback\Form::class)->create()->id,
        'account_id' => App\Models\Mship\Account::factory()->create()->id,
        'submitter_account_id' => App\Models\Mship\Account::factory()->create()->fresh()->id,
    ];
});

$factory->define(App\Models\Mship\Feedback\Answer::class, function (Faker $faker) {
    $feedback = factory(App\Models\Mship\Feedback\Feedback::class)->create();

    return [
        'feedback_id' => $feedback->id,
        'question_id' => factory(App\Models\Mship\Feedback\Question::class)->create([
            'form_id' => $feedback->id,
        ])->id,
        'response' => $faker->sentence,
    ];
});
