<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands\Training;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Mentoring\MentoringReportOutstandingNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckForPendingMentoringReportsCommandTest extends TestCase
{
    use DatabaseTransactions;

    private Account $mentorAccount;

    private Member $mentorMember;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentorAccount = Account::factory()->create();
        $this->mentorMember = Member::factory()->create([
            'id' => $this->mentorAccount->id,
            'cid' => $this->mentorAccount->id,
        ]);
    }

    private function createSession(array $overrides = []): Session
    {
        return Session::factory()->create(array_merge([
            'mentor_id' => $this->mentorMember->id,
            'filed' => null,
            'cancelled_datetime' => null,
            'noShow' => 0,
            'taken_date' => now()->subDays(4),
        ], $overrides));
    }

    #[Test]
    public function it_sends_notification_for_pending_reports_older_than_72_hours(): void
    {
        $this->createSession();

        $this->artisan('training:check-for-pending-mentoring-reports')->assertExitCode(0);
        Notification::assertSentTo($this->mentorAccount, MentoringReportOutstandingNotification::class);
    }

    #[Test]
    public function it_does_not_send_notification_for_filed_reports(): void
    {
        $this->createSession(['filed' => now()]);

        $this->artisan('training:check-for-pending-mentoring-reports')->assertExitCode(0);
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_does_not_send_notification_for_cancelled_sessions(): void
    {
        $this->createSession(['cancelled_datetime' => now()]);

        $this->artisan('training:check-for-pending-mentoring-reports')->assertExitCode(0);
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_does_not_send_notification_for_no_show_sessions(): void
    {
        $this->createSession(['noShow' => 1]);

        $this->artisan('training:check-for-pending-mentoring-reports')->assertExitCode(0);
        Notification::assertNothingSent();
    }

    #[Test]
    public function it_does_not_send_notification_for_sessions_within_72_hours(): void
    {
        $this->createSession(['taken_date' => now()->subHours(48)]);

        $this->artisan('training:check-for-pending-mentoring-reports')->assertExitCode(0);
        Notification::assertNothingSent();
    }
}
