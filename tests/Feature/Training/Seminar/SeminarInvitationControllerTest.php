<?php

declare(strict_types=1);

namespace Tests\Feature\Training\Seminar;

use App\Enums\SeminarInvitationStatus;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarInvitation;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarInvitationControllerTest extends TestCase
{
    use DatabaseTransactions;

    private Seminar $seminar;

    private Account $student;

    private SeminarInvitation $invitation;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        $this->student = Account::factory()->create();

        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $waitingList->addToWaitingList($this->student, $this->privacc);

        $this->seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => 10,
            'invitation_expiry_days' => 7,
            'created_by' => $this->privacc->id,
        ]);

        $this->invitation = SeminarInvitation::factory()->create([
            'seminar_id' => $this->seminar->id,
            'account_id' => $this->student->id,
            'token' => 'valid-test-token-1234567890',
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->addDays(5),
        ]);
    }

    #[Test]
    public function authenticated_member_can_accept_invitation(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', 'valid-test-token-1234567890'))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.result')
            ->assertViewHas('result', 'accepted');
    }

    #[Test]
    public function accept_updates_invitation_status_to_attending(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token));

        $this->assertEquals(
            SeminarInvitationStatus::Attending,
            $this->invitation->fresh()->status
        );
    }

    #[Test]
    public function accept_creates_attendee_record(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token));

        $this->assertDatabaseHas('training_seminar_attendees', [
            'seminar_id' => $this->seminar->id,
            'account_id' => $this->student->id,
            'invitation_id' => $this->invitation->id,
        ]);
    }

    #[Test]
    public function another_member_cannot_accept_someone_elses_invitation(): void
    {
        $otherMember = Account::factory()->create();

        $this->actingAs($otherMember)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token))
            ->assertForbidden();
    }

    #[Test]
    public function accept_shows_expired_view_when_invitation_expired(): void
    {
        $this->invitation->update(['expires_at' => now()->subDay()]);

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.expired');
    }

    #[Test]
    public function accept_returns_404_for_invalid_token(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', 'non-existent-token'))
            ->assertNotFound();
    }

    #[Test]
    public function accept_does_not_update_status_when_expired(): void
    {
        $this->invitation->update(['expires_at' => now()->subDay()]);

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token));

        $this->assertEquals(
            SeminarInvitationStatus::Sent,
            $this->invitation->fresh()->status
        );
    }

    #[Test]
    public function accept_shows_accepted_when_invitation_already_attending(): void
    {
        $this->invitation->update([
            'status' => SeminarInvitationStatus::Attending,
            'responded_at' => now(),
        ]);

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.result')
            ->assertViewHas('result', 'accepted');
    }

    #[Test]
    public function unauthenticated_user_redirected_to_login(): void
    {
        $this->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token))
            ->assertRedirect();
    }

    #[Test]
    public function authenticated_member_can_mark_not_interested(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.not-interested', $this->invitation->token))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.result')
            ->assertViewHas('result', 'not_interested');
    }

    #[Test]
    public function not_interested_updates_invitation_status(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.not-interested', $this->invitation->token));

        $this->assertEquals(
            SeminarInvitationStatus::NotInterested,
            $this->invitation->fresh()->status
        );
    }

    #[Test]
    public function another_member_cannot_mark_not_interested_for_someone_else(): void
    {
        $otherMember = Account::factory()->create();

        $this->actingAs($otherMember)
            ->get(route('mship.waiting-lists.seminar-invitation.not-interested', $this->invitation->token))
            ->assertForbidden();
    }

    #[Test]
    public function not_interested_shows_expired_view_when_expired(): void
    {
        $this->invitation->update(['expires_at' => now()->subDay()]);

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.not-interested', $this->invitation->token))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.expired');
    }

    #[Test]
    public function not_interested_returns_404_for_invalid_token(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.not-interested', 'non-existent-token'))
            ->assertNotFound();
    }

    #[Test]
    public function authenticated_member_can_mark_cannot_attend(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.cannot-attend', $this->invitation->token))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.result')
            ->assertViewHas('result', 'cannot_attend');
    }

    #[Test]
    public function cannot_attend_updates_invitation_status(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.cannot-attend', $this->invitation->token));

        $this->assertEquals(
            SeminarInvitationStatus::CannotAttend,
            $this->invitation->fresh()->status
        );
    }

    #[Test]
    public function another_member_cannot_mark_cannot_attend_for_someone_else(): void
    {
        $otherMember = Account::factory()->create();

        $this->actingAs($otherMember)
            ->get(route('mship.waiting-lists.seminar-invitation.cannot-attend', $this->invitation->token))
            ->assertForbidden();
    }

    #[Test]
    public function cannot_attend_shows_expired_view_when_expired(): void
    {
        $this->invitation->update(['expires_at' => now()->subDay()]);

        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.cannot-attend', $this->invitation->token))
            ->assertOk()
            ->assertViewIs('training.seminar-invitation.expired');
    }

    #[Test]
    public function cannot_attend_returns_404_for_invalid_token(): void
    {
        $this->actingAs($this->student)
            ->get(route('mship.waiting-lists.seminar-invitation.cannot-attend', 'non-existent-token'))
            ->assertNotFound();
    }

    #[Test]
    public function accept_returns_403_when_not_authenticated_as_owner(): void
    {
        $anotherMember = Account::factory()->create();

        $this->actingAs($anotherMember)
            ->get(route('mship.waiting-lists.seminar-invitation.accept', $this->invitation->token))
            ->assertForbidden();
    }

    #[Test]
    public function not_interested_returns_403_when_not_authenticated_as_owner(): void
    {
        $anotherMember = Account::factory()->create();

        $this->actingAs($anotherMember)
            ->get(route('mship.waiting-lists.seminar-invitation.not-interested', $this->invitation->token))
            ->assertForbidden();
    }

    #[Test]
    public function cannot_attend_returns_403_when_not_authenticated_as_owner(): void
    {
        $anotherMember = Account::factory()->create();

        $this->actingAs($anotherMember)
            ->get(route('mship.waiting-lists.seminar-invitation.cannot-attend', $this->invitation->token))
            ->assertForbidden();
    }
}
