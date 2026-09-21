<?php

declare(strict_types=1);

namespace Database\Factories\Training\Seminar;

use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\Seminar\SeminarInvitation;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeminarAttendeeFactory extends Factory
{
    protected $model = SeminarAttendee::class;

    public function definition(): array
    {
        return [
            'seminar_id' => Seminar::factory(),
            'account_id' => Account::factory(),
            'invitation_id' => null,
            'added_by' => null,
            'added_at' => now(),
        ];
    }

    public function withInvitation(): static
    {
        return $this->state(fn (array $attributes) => [
            'invitation_id' => SeminarInvitation::factory()->attending(),
        ]);
    }
}
