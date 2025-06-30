<?php

// use App\Models\Cts\Member;
// use Carbon\Carbon;
// use Faker\Generator as Faker;

// $factory->define(App\Models\Cts\Validation::class, function (Faker $faker) {
//     return [
//         'position_id' => factory(\App\Models\Cts\ValidationPosition::class)->create()->id,
//         'member_id' => Member::Factory()->create()->id,
//         'awarded_by' => Member::Factory()->create()->id,
//         'awarded_date' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
//     ];
// });

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\Validation;
use App\Models\Cts\ValidationPosition;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ValidationFactory extends Factory
{
    protected $model = Validation::class;

    public function definition(): array
    {
        return [
            'position_id' => ValidationPosition::Factory()->create()->id,
            'member_id' => Member::Factory()->create()->id,
            'awarded_by' => Member::Factory()->create()->id,
            'awarded_date' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
        ];
    }
}
