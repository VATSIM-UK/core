<?php

declare(strict_types=1);

namespace Tests\Feature\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\TrainingPlaceRemovedDueToExpiredAvailability;
use App\Notifications\Training\TrainingPlaceRemovedDueToFourthAvailabilityFailure;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpiredAvailabilityWarningFlowTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private Member $ctsMember;

    private TrainingPlace $trainingPlace;

    private Carbon $dayZero;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dayZero = $this->knownDate->copy()->startOfDay();
        Carbon::setTestNow($this->dayZero);

        $this->ctsMember = Member::factory()->create();
        $this->account = Account::factory()->create(['id' => $this->ctsMember->cid]);

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($this->account, Account::factory()->create());

        $trainingPosition = TrainingPosition::factory()->create([
            'cts_positions' => ['EGLL_APP', 'EGLL_TWR'],
        ]);

        $this->trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    public function it_removes_training_place_after_warning_expires_with_daily_checks_throughout(): void
    {
        // Day 0: First availability check fails → warning created (expires at end of day 5)
        Artisan::call('training-places:check-availability');

        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'failed',
        ]);
        $warning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->first();
        $this->assertNotNull($warning);
        $this->assertTrue($warning->expires_at->isFuture(), 'Warning should expire at end of day 5');
        $expiresAt = $warning->expires_at->copy();

        // Day 1: Daily check runs again – member still has no availability → no new warning (pending exists)
        Carbon::setTestNow($this->dayZero->copy()->addDay());
        Artisan::call('training-places:check-availability');

        $pendingCount = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->count();
        $this->assertSame(1, $pendingCount);
        $this->assertSame(2, $this->availabilityCheckCount(), 'Day 0 + day 1 failed checks');

        // Day 2: Daily check again
        Carbon::setTestNow($this->dayZero->copy()->addDays(2));
        Artisan::call('training-places:check-availability');
        $this->assertSame(3, $this->availabilityCheckCount());

        // Day 3: Daily check again
        Carbon::setTestNow($this->dayZero->copy()->addDays(3));
        Artisan::call('training-places:check-availability');
        $this->assertSame(4, $this->availabilityCheckCount());

        // Day 4: Daily check again
        Carbon::setTestNow($this->dayZero->copy()->addDays(4));
        Artisan::call('training-places:check-availability');
        $this->assertSame(5, $this->availabilityCheckCount());

        // Day 5: Still within window (expires end of day 5)
        Carbon::setTestNow($this->dayZero->copy()->addDays(5)->startOfDay());
        Artisan::call('training-places:check-availability');
        $this->assertSame(6, $this->availabilityCheckCount());
        $this->trainingPlace->refresh();
        $this->assertNull($this->trainingPlace->deleted_at, 'Training place must still exist before expiry');

        // Day 6: Past expiry – run expired-warning command → removal
        Carbon::setTestNow($expiresAt->copy()->addDay());
        Artisan::call('training-places:check-for-expired-availability-warnings');

        $this->trainingPlace->refresh();
        $this->assertNotNull($this->trainingPlace->deleted_at, 'Training place should be soft-deleted after expired warning is processed');

        $warning->refresh();
        $this->assertSame('expired', $warning->status);
        $this->assertNotNull($warning->removal_actioned_at);

        Notification::assertSentTo($this->account, TrainingPlaceRemovedDueToExpiredAvailability::class);
    }

    #[Test]
    public function it_removes_training_place_on_fourth_availability_failure_after_three_resolved_episodes(): void
    {
        // Episode 1: Fail → warning, then resolve within window
        Artisan::call('training-places:check-availability');
        $warning1 = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->where('status', 'pending')->first();
        $this->assertNotNull($warning1);

        Carbon::setTestNow($this->dayZero->copy()->addDay());
        $this->addAvailabilityAndSession();
        Artisan::call('training-places:check-availability');
        $passedCheck1 = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)->where('status', 'passed')->latest()->first();
        $this->assertNotNull($passedCheck1);
        $warning1->refresh();
        $this->assertSame('resolved', $warning1->status, 'Job should resolve pending warning when check passes');
        $this->assertSame($passedCheck1->id, $warning1->resolved_availability_check_id);
        $this->removeAvailabilityAndSession();

        // Episode 2: Fail → warning, then resolve within window (job auto-resolves on pass)
        Carbon::setTestNow($this->dayZero->copy()->addDays(2));
        Artisan::call('training-places:check-availability');
        $warning2 = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->where('status', 'pending')->first();
        $this->assertNotNull($warning2);

        Carbon::setTestNow($this->dayZero->copy()->addDays(3));
        $this->addAvailabilityAndSession();
        Artisan::call('training-places:check-availability');
        $passedCheck2 = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)->where('status', 'passed')->latest()->first();
        $this->assertNotNull($passedCheck2);
        $warning2->refresh();
        $this->assertSame('resolved', $warning2->status);
        $this->assertSame($passedCheck2->id, $warning2->resolved_availability_check_id);
        $this->removeAvailabilityAndSession();

        // Episode 3: Fail → warning, then resolve within window (job auto-resolves on pass)
        Carbon::setTestNow($this->dayZero->copy()->addDays(4));
        Artisan::call('training-places:check-availability');
        $warning3 = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->where('status', 'pending')->first();
        $this->assertNotNull($warning3);

        Carbon::setTestNow($this->dayZero->copy()->addDays(5));
        $this->addAvailabilityAndSession();
        Artisan::call('training-places:check-availability');
        $passedCheck3 = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)->where('status', 'passed')->latest()->first();
        $this->assertNotNull($passedCheck3);
        $warning3->refresh();
        $this->assertSame('resolved', $warning3->status);
        $this->assertSame($passedCheck3->id, $warning3->resolved_availability_check_id);
        $this->removeAvailabilityAndSession();

        $this->assertSame(3, AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->where('status', 'resolved')->count());

        // Episode 4: Fourth failure → immediate removal (no 5-day window)
        Carbon::setTestNow($this->dayZero->copy()->addDays(6));
        Artisan::call('training-places:check-availability');

        $this->trainingPlace->refresh();
        $this->assertNotNull($this->trainingPlace->deleted_at, 'Training place should be removed on fourth availability failure');

        $fourthWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->latest()->first();
        $this->assertNotNull($fourthWarning, 'Fourth warning should exist (created then processed by removal job)');
        $this->assertSame('expired', $fourthWarning->status, 'Removal job should mark the fourth warning as expired');

        Notification::assertSentTo($this->account, TrainingPlaceRemovedDueToFourthAvailabilityFailure::class);
    }

    /**
     * @return array<string, array{int}>
     */
    public static function recoveryDayProvider(): array
    {
        return [
            'day 1' => [1],
            'day 2' => [2],
            'day 3' => [3],
            'day 4' => [4],
            'day 5' => [5],
        ];
    }

    #[DataProvider('recoveryDayProvider')]
    #[Test]
    public function it_maintains_training_place_when_member_adds_availability_and_session_on_recovery_day(int $recoveryDay): void
    {
        // Day 0: Availability check fails → warning created
        Artisan::call('training-places:check-availability');

        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'failed',
        ]);
        $warning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'pending')
            ->first();
        $this->assertNotNull($warning);

        // Days 1 to recoveryDay - 1: Still no availability → check fails each day
        for ($day = 1; $day < $recoveryDay; $day++) {
            Carbon::setTestNow($this->dayZero->copy()->addDays($day));
            Artisan::call('training-places:check-availability');
            $this->assertSame($day + 1, $this->availabilityCheckCount(), "After day {$day} should have ".($day + 1).' failed checks');
        }

        // Recovery day: Member adds session request and availability → check passes, job resolves warning, training place maintained
        Carbon::setTestNow($this->dayZero->copy()->addDays($recoveryDay));
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);
        Artisan::call('training-places:check-availability');

        $expectedCheckCount = $recoveryDay + 1;
        $this->assertSame($expectedCheckCount, $this->availabilityCheckCount());
        $this->assertDatabaseHas('availability_checks', [
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'passed',
        ]);
        $warning->refresh();
        $this->assertSame('resolved', $warning->status, 'Job should resolve pending warning when check passes on recovery');
        $passedCheck = AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)->where('status', 'passed')->first();
        $this->assertSame($passedCheck->id, $warning->resolved_availability_check_id);
        $this->trainingPlace->refresh();
        $this->assertNull($this->trainingPlace->deleted_at, "Training place should be maintained when availability and session are added on day {$recoveryDay}");
    }

    private function availabilityCheckCount(): int
    {
        return AvailabilityCheck::where('training_place_id', $this->trainingPlace->id)->count();
    }

    private function addAvailabilityAndSession(): void
    {
        Availability::factory()->forStudent($this->ctsMember->id)->create();
        Session::factory()->create([
            'student_id' => $this->ctsMember->id,
            'position' => 'EGLL_APP',
        ]);
    }

    private function removeAvailabilityAndSession(): void
    {
        Availability::where('student_id', $this->ctsMember->id)->delete();
        Session::where('student_id', $this->ctsMember->id)->delete();
    }
}
