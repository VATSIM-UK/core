<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Training\Mentoring;

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Notifications\Training\Mentoring\MentoringReportOutstandingNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringReportOutstandingNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $mentorAccount;

    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mentorAccount = Account::factory()->create();
        $mentorMember = Member::factory()->create([
            'id' => $this->mentorAccount->id,
            'cid' => $this->mentorAccount->id,
        ]);

        $this->session = Session::factory()->create([
            'mentor_id' => $mentorMember->id,
            'taken_date' => now()->subDays(4),
        ]);
    }

    #[Test]
    public function it_sends_via_mail_channel(): void
    {
        $notification = new MentoringReportOutstandingNotification($this->session);

        $this->assertContains('mail', $notification->via($this->mentorAccount));
    }
}
