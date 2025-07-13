<?php

namespace Database\Factories\Cts;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<ExamBooking>
 */
class ExamBookingFactory extends Factory
{
    protected $model = ExamBooking::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $account = Account::factory()->create();
        $studentMember = factory(Member::class)->create(
            ['id' => $account->id, 'cid' => $account->id]
        );

        return [
            'position_1' => 'EGKK_TWR',
            'taken' => 1,
            'finished' => 0,
            'taken_date' => Carbon::parse('2026-01-01')->format('Y-m-d'),
            'taken_from' => Carbon::parse('2026-01-01 12:00:00')->format('H:i:s'),
            'taken_to' => Carbon::parse('2026-01-01 13:00:00')->format('H:i:s'),
            'student_rating' => 1,
            'student_id' => $studentMember->id,
        ];
    }
}
