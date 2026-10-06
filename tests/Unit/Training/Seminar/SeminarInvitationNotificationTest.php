<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList;
use App\Notifications\Training\SeminarInvitationNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarInvitationNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Account $account;

    private Seminar $seminar;

    private SeminarInvitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);

        $this->seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'created_by' => $this->privacc->id,
        ]);

        $this->account = Account::factory()->create();

        $this->invitation = SeminarInvitation::factory()->create([
            'seminar_id' => $this->seminar->id,
            'account_id' => $this->account->id,
            'expires_at' => now()->addDays(5),
        ]);
    }

    #[Test]
    public function normal_renders_normal_copy_and_response_buttons(): void
    {
        $html = $this->render($this->invitation);

        $this->assertStringContainsString('that a place is available for you on the next S1 Group Seminar', $html);
        $this->assertStringContainsString('If you fail to action this e-mail, you will be afforded one more opportunity', $html);
        $this->assertStringContainsString('If you fail to action the second invitation, or if you confirm your attendance', $html);
        $this->assertStringContainsString('PLEASE NOTE', $html);
        $this->assertStringContainsString('If you do not accept the second invitation your place on the waiting list will be removed.', $html);
        $this->assertStringNotContainsString('SHORT NOTICE', $html);
        $this->assertStringNotContainsString("'short notice' place", $html);

        $this->assertButtonsPresent($html);
    }

    #[Test]
    public function short_notice_renders_short_notice_copy_and_response_buttons(): void
    {
        $this->invitation->update(['is_short_notice' => true]);

        $html = $this->render($this->invitation->fresh());

        $this->assertStringContainsString("a 'short notice' place is available for you on the next S1 Group Seminar", $html);
        $this->assertStringContainsString('Given that this is a \'short notice\' offer', $html);
        $this->assertStringNotContainsString('If you fail to action this e-mail', $html);
        $this->assertStringNotContainsString('PLEASE NOTE', $html);
        $this->assertStringNotContainsString('one more opportunity to attend a group session', $html);

        $this->assertButtonsPresent($html);
    }

    private function assertButtonsPresent(string $html): void
    {
        $this->assertStringContainsString(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token), $html);
        $this->assertStringContainsString(route('mship.waiting-lists.seminar-invitation.cannot-attend', $this->invitation->token), $html);
        $this->assertStringContainsString(route('mship.waiting-lists.seminar-invitation.not-interested', $this->invitation->token), $html);

        $this->assertStringContainsString('#5cb85c', $html);
        $this->assertStringContainsString('#f0ad4e', $html);
        $this->assertStringContainsString('#d9534f', $html);
    }

    private function render(SeminarInvitation $invitation): string
    {
        $mail = (new SeminarInvitationNotification($invitation))->toMail($this->account);

        return preg_replace('/\s+/', ' ', $this->renderMail($mail));
    }

    private function renderMail(MailMessage $mail): string
    {
        return view($mail->view, array_merge($mail->viewData, [
            'subject' => $mail->subject,
        ]))->render();
    }
}
