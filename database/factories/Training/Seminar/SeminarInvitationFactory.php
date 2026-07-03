<?php

declare(strict_types=1);

namespace Database\Factories\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SeminarInvitationFactory extends Factory
{
    protected $model = SeminarInvitation::class;

    public function definition(): array
    {
        return [
            'seminar_id' => Seminar::factory(),
            'account_id' => Account::factory(),
            'waiting_list_account_id' => null,
            'token' => Str::random(32),
            'status' => SeminarInvitationStatus::Sent,
            'sent_at' => now(),
            'responded_at' => null,
            'expires_at' => now()->addDays(7),
        ];
    }

    public function attending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SeminarInvitationStatus::Attending,
            'responded_at' => now(),
        ]);
    }

    public function notInterested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SeminarInvitationStatus::NotInterested,
            'responded_at' => now(),
        ]);
    }

    public function cannotAttend(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SeminarInvitationStatus::CannotAttend,
            'responded_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SeminarInvitationStatus::Expired,
            'responded_at' => now(),
        ]);
    }

    public function removedNoResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SeminarInvitationStatus::RemovedNoResponse,
            'responded_at' => now(),
        ]);
    }

    public function withWaitingListAccount(): static
    {
        return $this->state(fn (array $attributes) => [
            'waiting_list_account_id' => WaitingListAccount::factory(),
        ]);
    }
}
