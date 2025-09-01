<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cts\ExamSetup>
 */
class ExamSetupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'rts_id' => 14,
            'student_id' => Member::Factory()->create()->id,
            'position_1' => 'OBS_SC_PT3',
            'position_2' => null,
            'exam' => 'OBS',
            'setup_by' => Member::Factory()->create()->id,
            'setup_date' => Carbon::createFromFormat('Y-m-d H:i:s', now())->toDateTimeString(),
        ];
    }
}
