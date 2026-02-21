<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Training;

use App\Jobs\Training\ActionFourthAvailabilityFailureRemoval;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\TrainingPlaceRemovedDueToExpiredAvailability;
use App\Notifications\Training\TrainingPlaceRemovedDueToFourthAvailabilityFailure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActionFourthAvailabilityFailureRemovalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    private function createFourthFailureScenario(): array
    {
        $ctsMember = Member::factory()->create();
        $account = Account::factory()->create(['id' => $ctsMember->cid]);
        $waitingList = WaitingList::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($account, Account::factory()->create());
        $trainingPosition = TrainingPosition::factory()->create();
        $trainingPlace = TrainingPlace::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);
        $availabilityCheck = AvailabilityCheck::factory()->failed()->create([
            'training_place_id' => $trainingPlace->id,
        ]);
        $warning = AvailabilityWarning::factory()->pending()->create([
            'training_place_id' => $trainingPlace->id,
            'availability_check_id' => $availabilityCheck->id,
            'expires_at' => now(),
        ]);

        return ['account' => $account, 'trainingPlace' => $trainingPlace, 'warning' => $warning];
    }

    #[Test]
    public function it_removes_training_place_and_marks_warning_expired(): void
    {
        $scenario = $this->createFourthFailureScenario();

        $job = new ActionFourthAvailabilityFailureRemoval($scenario['warning']);
        $job->handle();

        $this->assertSoftDeleted('training_places', ['id' => $scenario['trainingPlace']->id]);
        $scenario['warning']->refresh();
        $this->assertEquals('expired', $scenario['warning']->status);
        $this->assertNotNull($scenario['warning']->removal_actioned_at);
    }

    #[Test]
    public function it_sends_fourth_failure_notification_to_account(): void
    {
        $scenario = $this->createFourthFailureScenario();

        $job = new ActionFourthAvailabilityFailureRemoval($scenario['warning']);
        $job->handle();

        Notification::assertSentTo(
            $scenario['account'],
            TrainingPlaceRemovedDueToFourthAvailabilityFailure::class,
            function ($notification) use ($scenario) {
                return $notification->availabilityWarning->id === $scenario['warning']->id;
            }
        );
        Notification::assertNotSentTo($scenario['account'], TrainingPlaceRemovedDueToExpiredAvailability::class);
    }

    #[Test]
    public function it_skips_when_warning_is_no_longer_pending(): void
    {
        $scenario = $this->createFourthFailureScenario();
        $scenario['warning']->update(['status' => 'expired']);

        $job = new ActionFourthAvailabilityFailureRemoval($scenario['warning']);
        $job->handle();

        Notification::assertNothingSent();
        $scenario['trainingPlace']->refresh();
        $this->assertNull($scenario['trainingPlace']->deleted_at);
    }
}
