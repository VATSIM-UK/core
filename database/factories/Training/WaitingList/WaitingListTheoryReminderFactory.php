<?php

namespace Database\Factories\Training\WaitingList;

use App\Enums\TheoryExamReminderStatus;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListTheoryReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\WaitingList\WaitingListTheoryReminder>
 */
class WaitingListTheoryReminderFactory extends Factory
{
    protected $model = WaitingListTheoryReminder::class;

    public function definition(): array
    {
        $account = Account::factory()->create();
        $waitingList = WaitingList::factory()->create();

        $waitingListAccount = WaitingListAccount::query()->forceCreate([
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
        ]);

        return [
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $account->id,
            'seminar_id' => null,
            'status' => TheoryExamReminderStatus::Pending,
            'reminded_at' => now(),
            'last_attempt_at' => null,
            'next_check_at' => now()->addDays(WaitingListTheoryReminder::COMPLETION_WINDOW_DAYS),
            'resolved_at' => null,
        ];
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'next_check_at' => now()->subDay(),
        ]);
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TheoryExamReminderStatus::Passed,
            'resolved_at' => now(),
        ]);
    }
}
