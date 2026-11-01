<?php

namespace Tests\Feature\VisitTransfer;

use App\Events\VisitTransfer\ApplicationSubmitted;
use App\Listeners\VisitTransfer\NotifyApplicantOfStatusChange;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Queue\Jobs\SyncJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WithdrawalNotificationTest extends TestCase
{
    #[Test]
    public function queued_submission_listener_survives_withdrawal_before_it_runs(): void
    {
        $application = $this->application();
        $account = $application->account;
        $payload = serialize(new CallQueuedListener(NotifyApplicantOfStatusChange::class, 'handle', [new ApplicationSubmitted($application)]));

        $application->withdraw();
        $job = unserialize($payload);
        $this->assertInstanceOf(CallQueuedListener::class, $job);
        $job->setJob(new SyncJob($this->app, '{}', 'sync', 'default'));
        $job->handle($this->app);

        Notification::assertSentToTimes($account, ApplicationStatusChanged::class, 1);
        Notification::assertSentTo($account, ApplicationStatusChanged::class, fn ($notification) => str_contains($notification->toMail($account)->subject, 'Withdrawn'));
    }

    #[Test]
    public function queued_status_notification_survives_withdrawal_and_sends_no_stale_channels(): void
    {
        Mail::fake();
        $application = $this->application();
        $account = $application->account;
        $notificationsBefore = $account->notifications()->count();
        $payload = serialize(new SendQueuedNotifications($account, new ApplicationStatusChanged($application), ['mail', 'database']));

        $application->withdraw();
        $job = unserialize($payload);
        $this->assertInstanceOf(SendQueuedNotifications::class, $job);
        $job->handle(new ChannelManager($this->app));

        Mail::assertNothingOutgoing();
        $this->assertSame($notificationsBefore, $account->notifications()->count());
        $this->assertFalse($job->notification->shouldSend($account, 'mail'));
        $this->assertFalse($job->notification->shouldSend($account, 'database'));
    }

    public static function applicationTypes(): iterable
    {
        foreach (['atc', 'pilot'] as $team) {
            yield "$team visit with facility" => [Application::TYPE_VISIT, true, $team];
            yield "$team transfer with facility" => [Application::TYPE_TRANSFER, true, $team];
            yield "$team visit without facility" => [Application::TYPE_VISIT, false, $team];
            yield "$team transfer without facility" => [Application::TYPE_TRANSFER, false, $team];
        }
    }

    #[Test]
    #[DataProvider('applicationTypes')]
    public function real_dispatch_queues_a_serializable_snapshot_only_after_commit(int $type, bool $hasFacility, string $team): void
    {
        config(['queue.default' => 'database', 'mail.default' => 'array']);
        Notification::swap(new ChannelManager($this->app));
        $application = $this->application(['type' => $type, 'training_team' => $team]);
        if (! $hasFacility) {
            $application->facility_id = null;
            $application->saveQuietly();
        }
        $account = $application->account;
        $publicId = $application->public_id;
        $typeString = $application->type_string;
        $facility = $application->facility;
        $facilityName = $facility?->name;
        $oldUrl = route('visiting.application.view', [$publicId]);
        $previousJobIds = DB::table('jobs_pending')->pluck('id');

        DB::transaction(function () use ($application, $previousJobIds) {
            $application->withdraw();
            $this->assertDatabaseMissing('vt_application', ['id' => $application->id]);
            $this->assertSame(0, DB::table('jobs_pending')->whereNotIn('id', $previousJobIds)->count());
        });

        $queued = DB::table('jobs_pending')->whereNotIn('id', $previousJobIds)->get();
        $this->assertCount(2, $queued);
        if ($facility) {
            DB::table($facility->getTable())->where('id', $facility->id)->update(['name' => 'Renamed after withdrawal']);
            DB::table($facility->getTable())->where('id', $facility->id)->delete();
        }
        foreach ($queued as $record) {
            $payload = json_decode($record->payload, true, flags: JSON_THROW_ON_ERROR);
            $job = unserialize($payload['data']['command']);
            $this->assertInstanceOf(SendQueuedNotifications::class, $job);
            $this->assertInstanceOf(ApplicationStatusChanged::class, $job->notification);
            $this->assertStringNotContainsString(Application::class, serialize($job->notification));
            $this->assertTrue($job->afterCommit);
            $message = $job->notification->toMail($account);
            $this->assertStringContainsString($publicId, $message->subject);
            $this->assertStringContainsString($typeString, $message->subject);
            $html = view($message->view, $message->viewData)->render();
            $this->assertStringContainsString($typeString, $html);
            if ($facilityName) {
                $this->assertStringContainsString(e($facilityName), $html);
            }
            $this->assertStringNotContainsString('Renamed after withdrawal', $html);
            $this->assertStringNotContainsString('Unknown Visit', $html);
            $this->assertStringNotContainsString('Unknown Transfer', $html);
            $this->assertStringNotContainsString($oldUrl, $html);
            $this->assertStringNotContainsString('/application/'.$publicId, $html);
            $job->handle($this->app->make(ChannelManager::class));
        }
        $sent = Mail::mailer('array')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $email = $sent->sole()->getOriginalMessage();
        $this->assertSame($account->email, $email->getTo()[0]->getAddress());
        $this->assertStringContainsString($publicId, $email->getSubject());
        $this->assertStringContainsString($typeString, $email->getHtmlBody());
        if ($facilityName) {
            $this->assertStringContainsString(e($facilityName), $email->getHtmlBody());
        }
        $this->assertStringNotContainsString($oldUrl, $email->getHtmlBody());
        $this->assertSame(1, $account->notifications()->where('type', ApplicationStatusChanged::class)->count());
    }

    #[Test]
    public function submission_still_queues_its_listener_and_delivers_the_original_status_email(): void
    {
        config(['queue.default' => 'database', 'mail.default' => 'array']);
        Notification::swap(new ChannelManager($this->app));
        $application = $this->application();
        $account = $application->account;
        $previousJobIds = DB::table('jobs_pending')->pluck('id');

        event(new ApplicationSubmitted($application));

        $listeners = DB::table('jobs_pending')->whereNotIn('id', $previousJobIds)->get();
        $this->assertCount(1, $listeners);
        $payload = json_decode($listeners->sole()->payload, true, flags: JSON_THROW_ON_ERROR);
        $listener = unserialize($payload['data']['command']);
        $this->assertInstanceOf(CallQueuedListener::class, $listener);
        $this->assertSame(NotifyApplicantOfStatusChange::class, $listener->class);
        $this->assertSame(0, $account->notifications()->count());
        $this->assertCount(0, Mail::mailer('array')->getSymfonyTransport()->messages());

        $listener->setJob(new SyncJob($this->app, '{}', 'sync', 'default'));
        $listener->handle($this->app);

        $notifications = DB::table('jobs_pending')->whereNotIn('id', $previousJobIds)->where('id', '!=', $listeners->sole()->id)->get();
        $this->assertCount(2, $notifications);
        foreach ($notifications as $record) {
            $payload = json_decode($record->payload, true, flags: JSON_THROW_ON_ERROR);
            $job = unserialize($payload['data']['command']);
            $this->assertInstanceOf(SendQueuedNotifications::class, $job);
            $job->handle($this->app->make(ChannelManager::class));
        }
        $email = Mail::mailer('array')->getSymfonyTransport()->messages()->sole()->getOriginalMessage();
        $this->assertSame($account->email, $email->getTo()[0]->getAddress());
        $this->assertStringContainsString('Application Submitted', $email->getSubject());
        $this->assertStringContainsString('automated checks', $email->getHtmlBody());
        $this->assertStringContainsString(route('visiting.application.view', [$application->public_id]), $email->getHtmlBody());
        $this->assertSame(1, $account->notifications()->where('type', ApplicationStatusChanged::class)->count());
    }

    #[Test]
    public function outer_transaction_rollback_preserves_application_and_discards_withdrawal_notification(): void
    {
        config(['queue.default' => 'database']);
        Notification::swap(new ChannelManager($this->app));
        $application = $this->application();
        $jobsBefore = DB::table('jobs_pending')->count();

        DB::beginTransaction();
        try {
            $application->withdraw();
            $this->assertDatabaseMissing('vt_application', ['id' => $application->id]);
            $this->assertSame($jobsBefore, DB::table('jobs_pending')->count());
        } finally {
            DB::rollBack();
        }

        $this->assertDatabaseHas('vt_application', ['id' => $application->id, 'status' => Application::STATUS_SUBMITTED]);
        $this->assertSame($jobsBefore, DB::table('jobs_pending')->count());
    }

    #[Test]
    public function unrelated_missing_models_are_not_silently_treated_as_deleted_applications(): void
    {
        $application = $this->application();
        $identifier = new ModelIdentifier(Account::class, Account::max('id') + 1, [], null);

        foreach ([new ApplicationSubmitted($application), new ApplicationStatusChanged($application)] as $serialized) {
            try {
                $serialized->restoreModel($identifier);
                $this->fail('Only missing applications should be tolerated.');
            } catch (ModelNotFoundException $exception) {
                $this->assertSame(Account::class, $exception->getModel());
            }
        }
    }

    #[Test]
    public function a_soft_deleted_applicant_does_not_prevent_withdrawal_or_queue_mail(): void
    {
        config(['queue.default' => 'database']);
        Notification::swap(new ChannelManager($this->app));
        $application = $this->application();
        $application->account->deleteQuietly();
        $jobsBefore = DB::table('jobs_pending')->count();

        $application->withdraw();

        $this->assertDatabaseMissing('vt_application', ['id' => $application->id]);
        $this->assertSame($jobsBefore, DB::table('jobs_pending')->count());
    }

    #[Test]
    public function failed_deletion_does_not_queue_a_withdrawal_notification(): void
    {
        config(['queue.default' => 'database']);
        Notification::swap(new ChannelManager($this->app));
        $application = $this->application();
        $jobsBefore = DB::table('jobs_pending')->count();
        Application::forceDeleting(function () {
            throw new \RuntimeException('Deletion failed');
        });

        try {
            $application->withdraw();
            $this->fail('The deletion failure must propagate.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Deletion failed', $exception->getMessage());
            $this->assertSame($jobsBefore, DB::table('jobs_pending')->count());
        }
    }

    private function application(array $attributes = []): Application
    {
        return Application::factory()->createQuietly(array_merge([
            'account_id' => Account::factory()->createQuietly()->id,
            'facility_id' => Facility::factory()->createQuietly(['name' => 'Withdrawal <Test> & Facility'])->id,
            'type' => Application::TYPE_VISIT,
            'training_team' => 'atc',
            'status' => Application::STATUS_SUBMITTED,
        ], $attributes));
    }
}
