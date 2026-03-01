<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Training;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Services\Training\AvailabilityWarnings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityWarningsTest extends TestCase
{
    use DatabaseTransactions;

    private function createTrainingPlaceWithFullRelations(): TrainingPlace
    {
        $account = Account::factory()->create();
        Member::factory()->create(['cid' => $account->id]);

        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($account, $account);
        $trainingPosition = TrainingPosition::factory()->create(['cts_positions' => []]);

        return TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
    }

    #[Test]
    public function it_returns_only_pending_warnings_that_have_expired(): void
    {
        $trainingPlace = $this->createTrainingPlaceWithFullRelations();
        $check = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);

        $expiredPending = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->subDay(),
        ]);

        AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->addDays(1),
        ]);

        AvailabilityWarning::factory()->expired()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->subDay(),
        ]);

        $result = AvailabilityWarnings::getExpiredPendingWarnings(now());

        $this->assertCount(1, $result);
        $this->assertEquals($expiredPending->id, $result->first()->id);
    }

    #[Test]
    public function it_returns_empty_when_no_expired_pending_warnings(): void
    {
        $result = AvailabilityWarnings::getExpiredPendingWarnings(now());

        $this->assertCount(0, $result);
    }

    #[Test]
    public function it_uses_given_date_for_expiry_comparison(): void
    {
        $trainingPlace = $this->createTrainingPlaceWithFullRelations();
        $check = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);

        AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => Carbon::parse('2025-01-15 23:59:59'),
        ]);

        $before = AvailabilityWarnings::getExpiredPendingWarnings(Carbon::parse('2025-01-14'));
        $after = AvailabilityWarnings::getExpiredPendingWarnings(Carbon::parse('2025-01-16'));

        $this->assertCount(0, $before);
        $this->assertCount(1, $after);
    }

    #[Test]
    public function it_marks_warning_as_expired(): void
    {
        $trainingPlace = $this->createTrainingPlaceWithFullRelations();
        $check = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);
        $warning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $check->id,
            'expires_at' => now()->subDay(),
        ]);

        $updated = AvailabilityWarnings::markWarningAsExpired($warning);

        $this->assertEquals('expired', $updated->status);
        $this->assertNotNull($updated->removal_actioned_at);
    }

    #[Test]
    public function it_marks_warning_as_resolved(): void
    {
        $trainingPlace = $this->createTrainingPlaceWithFullRelations();
        $failedCheck = AvailabilityCheck::factory()->failed()->create(['training_place_id' => $trainingPlace->id]);
        $passedCheck = AvailabilityCheck::factory()->passed()->create(['training_place_id' => $trainingPlace->id]);
        $warning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $failedCheck->id,
            'expires_at' => now()->addDays(1),
        ]);

        $updated = AvailabilityWarnings::markWarningAsResolved($warning, $passedCheck->id);

        $this->assertEquals('resolved', $updated->status);
        $this->assertNotNull($updated->resolved_at);
        $this->assertEquals($passedCheck->id, $updated->resolved_availability_check_id);
    }
}
