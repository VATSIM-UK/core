<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Booking;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $from = $this->faker->time();

        return [
            'date' => $this->faker->dateTimeInInterval('+1 YEAR')->format('Y-m-d'),
            'from' => $from,
            'to' => Carbon::createFromTimeString($from)->addHours(rand(1, 4))->toTimeString(),
            'position' => $this->faker->randomElement(['EGKK_APP', 'EGCC_APP', 'LON_SC_CTR', 'EGGP_GND']),
            'member_id' => Member::Factory()->create()->id,
            'type' => $this->faker->randomElement(['BK', 'EX', 'ME', 'EV']),
        ];
    }
}
