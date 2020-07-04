<?php

namespace Tests\Unit\Cts\Listeners;

use App\Events\Cts\StudentFailedSessionRequestCheck;
use App\Listeners\Cts\CreateSessionRequestCheckFailureAndNotify;
use App\Models\Mship\Account;
use App\Models\Training\SessionRequestCheck;
use App\Notifications\Training\FirstSessionCheckWarning;
use App\Notifications\Training\SecondSessionCheckWarning;
use App\Notifications\Training\TrainingDepartmentSessionCheckFailure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateSessionRequestCheckFailureListenerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itShouldNotCreateANewCheckIfActiveCheckAlreadyFound()
    {
        $activeCheck = factory(SessionRequestCheck::class)->create();

        $event = $this->mockStudentFailedSessionRequestEvent($activeCheck->account, $activeCheck->rts_id);

        $listener = new CreateSessionRequestCheckFailureAndNotify();
        $listener->handle($event);

        $this->assertCount(1, SessionRequestCheck::all());
    }

    /** @test */
    public function itShouldCreateANewCheckIfNoActiveChecksAreFound()
    {
        $this->assertCount(0, SessionRequestCheck::all());
        $account = factory(Account::class)->create();

        $event = $this->mockStudentFailedSessionRequestEvent($account, 1);
        $listener = new CreateSessionRequestCheckFailureAndNotify();
        $listener->handle($event);

        $this->assertCount(1, SessionRequestCheck::all());
    }

    /** @test */
    public function itShouldRespectSoftDeletesAndCreateCheckIfExistingCheckIsSoftDeleted()
    {
        $this->assertCount(0, SessionRequestCheck::all());
        $inactiveCheck = factory(SessionRequestCheck::class)->create(['deleted_at' => now()]);

        $event = $this->mockStudentFailedSessionRequestEvent($inactiveCheck->account, $inactiveCheck->rts_id);
        $this->createAndHandleListener($event);

        $this->assertCount(1, SessionRequestCheck::all());
    }

    /** @test */
    public function itShouldDispatchFirstNotificationWhenTheStageIsZeroAndIncrement()
    {
        Notification::fake();

        $check = factory(SessionRequestCheck::class)->create(['stage' => 0]);

        $event = $this->mockStudentFailedSessionRequestEvent($check->account, $check->rts_id);
        $this->createAndHandleListener($event);

        Notification::assertSentTo($check->account, FirstSessionCheckWarning::class);
        $this->assertEquals(1, $check->fresh()->stage);
    }

    /** @test */
    public function itShouldDispatchSecondNotificationWhenTheStageIsOneAndIncrement()
    {
        Notification::fake();

        $check = factory(SessionRequestCheck::class)->create(['stage' => 1]);

        $event = $this->mockStudentFailedSessionRequestEvent($check->account, $check->rts_id);
        $this->createAndHandleListener($event);

        Notification::assertSentTo($check->account, SecondSessionCheckWarning::class);
        $this->assertEquals(2, $check->fresh()->stage);
    }

    /** @test */
    public function itShouldDispatchFinalNotification()
    {
        Notification::fake();

        $trainingDepartment = 'atc-training@vatsim.uk';

        $check = factory(SessionRequestCheck::class)->create(['stage' => 2]);

        $event = $this->mockStudentFailedSessionRequestEvent($check->account, $check->rts_id);
        $this->createAndHandleListener($event);

        Notification::assertSentTo(new AnonymousNotifiable, TrainingDepartmentSessionCheckFailure::class, function ($notification, $channels, $notifiable) use ($trainingDepartment) {
            return $notifiable->routes['mail'] === $trainingDepartment;
        });
        $this->assertEquals(3, $check->fresh()->stage);
    }

    private function mockStudentFailedSessionRequestEvent(Account $account, int $rtsId)
    {
        $event = \Mockery::mock(StudentFailedSessionRequestCheck::class);
        $event->account = $account;
        $event->rtsId = $rtsId;

        return $event;
    }

    private function createAndHandleListener(StudentFailedSessionRequestCheck $event)
    {
        $listener = new CreateSessionRequestCheckFailureAndNotify();
        $listener->handle($event);
    }
}
