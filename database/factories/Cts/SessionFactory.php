<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\Session>
 */
class SessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rts_id' => 1,
            'position' => 'EGLL_APP',
            'student_id' => Member::Factory()->create()->id,
            'student_rating' => 5,
            'request_time' => now(),
            'progress_sheet_id' => 0,
        ];
    }

    public function accepted(): Factory
    {
        return $this->state([
            'mentor_id' => Member::Factory()->create()->id,
            'mentor_rating' => 5,
            'taken_time' => now(),
            'taken_date' => now(),
            'taken_from' => now()->addHour(),
            'taken_to' => now()->addHours(2),
        ]);
    }
}
