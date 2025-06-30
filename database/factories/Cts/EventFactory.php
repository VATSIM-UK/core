<?php

// use App\Models\Cts\Member;
// use Carbon\Carbon;
// use Faker\Generator as Faker;

// $factory->define(App\Models\Cts\Event::class, function (Faker $faker) {
//     $from = $faker->time();

//     return [
//         'event' => $faker->sentence,
//         'tagline' => 'event tagline',
//         'date' => $faker->dateTimeInInterval('+1 YEAR')->format('Y-m-d'),
//         'from' => $from,
//         'to' => Carbon::createFromTimeString($from)->addHours(rand(1, 4))->toTimeString(),
//         'add_by' => Member::Factory()->create()->id,
//         'text' => $faker->paragraph,
//         'thread' => $faker->url,
//     ];
// });

namespace Database\Factories\Cts;

use App\Models\Cts\Event;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $from = $this->faker->time();

        return [
            'event' => $this->faker->sentence,
            'tagline' => 'event tagline',
            'date' => $this->faker->dateTimeInInterval('+1 year')->format('Y-m-d'),
            'from' => $from,
            'to' => Carbon::createFromTimeString($from)->addHours(rand(1, 4))->toTimeString(),
            'add_by' => Member::factory(),
            'text' => $this->faker->paragraph->create()->id,
            'thread' => $this->faker->url,
        ];
    }
}
