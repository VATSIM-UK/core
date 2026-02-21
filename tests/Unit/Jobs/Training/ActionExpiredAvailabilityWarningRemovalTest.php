<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Training;

use App\Jobs\Training\ActionExpiredAvailabilityWarningRemoval;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList;
use App\Notifications\Training\TrainingPlaceRemovedDueToExpiredAvailability;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActionExpiredAvailabilityWarningRemovalTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // fake events to stop TrainingPlaceObserver from sending notifications
        Event::fake();

        Notification::fake();
    }

    private function createExpiredWarningScenario(): array
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
            'expires_at' => now()->subDay(),
        ]);

        return ['account' => $account, 'trainingPlace' => $trainingPlace, 'warning' => $warning];
    }

    #[Test]
    public function it_removes_training_place_and_marks_warning_expired_when_no_subsequent_passed_check(): void
    {
        $scenario = $this->createExpiredWarningScenario();

        $job = new ActionExpiredAvailabilityWarningRemoval($scenario['warning']);
        $job->handle();

        $this->assertSoftDeleted('training_places', ['id' => $scenario['trainingPlace']->id]);
        $scenario['warning']->refresh();
        $this->assertEquals('expired', $scenario['warning']->status);
        $this->assertNotNull($scenario['warning']->removal_actioned_at);
    }

    #[Test]
    public function it_sends_notification_to_account_when_removing_training_place(): void
    {
        $scenario = $this->createExpiredWarningScenario();

        $job = new ActionExpiredAvailabilityWarningRemoval($scenario['warning']);
        $job->handle();

        Notification::assertSentTo(
            $scenario['account'],
            TrainingPlaceRemovedDueToExpiredAvailability::class,
            function ($notification) use ($scenario) {
                return $notification->availabilityWarning->id === $scenario['warning']->id;
            }
        );
    }

    #[Test]
    public function it_skips_when_warning_is_no_longer_pending(): void
    {
        Notification::fake();
        $scenario = $this->createExpiredWarningScenario();
        $scenario['warning']->update(['status' => 'expired']);

        $job = new ActionExpiredAvailabilityWarningRemoval($scenario['warning']);
        $job->handle();

        Notification::assertNothingSent();
        $scenario['trainingPlace']->refresh();
        $this->assertNull($scenario['trainingPlace']->deleted_at);
    }

    #[Test]
    public function it_skips_when_warning_has_not_expired(): void
    {
        $scenario = $this->createExpiredWarningScenario();
        $scenario['warning']->update(['expires_at' => now()->addDays(1)]);

        $job = new ActionExpiredAvailabilityWarningRemoval($scenario['warning']);
        $job->handle();

        Notification::assertNothingSent();
        $scenario['trainingPlace']->refresh();
        $this->assertNull($scenario['trainingPlace']->deleted_at);
    }

    #[Test]
    public function it_does_not_resolve_when_passed_check_was_before_warning(): void
    {
        $scenario = $this->createExpiredWarningScenario();
        AvailabilityCheck::factory()->passed()->create([
            'training_place_id' => $scenario['trainingPlace']->id,
            'created_at' => $scenario['warning']->created_at->subHour(),
        ]);

        $job = new ActionExpiredAvailabilityWarningRemoval($scenario['warning']);
        $job->handle();

        Notification::assertSentTo($scenario['account'], TrainingPlaceRemovedDueToExpiredAvailability::class);
        $this->assertSoftDeleted('training_places', ['id' => $scenario['trainingPlace']->id]);
        $scenario['warning']->refresh();
        $this->assertEquals('expired', $scenario['warning']->status);
    }
}
