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

class CheckForExpiredSeminarInvitationsCommandTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();
    }

    #[Test]
    public function command_calls_expire_unresponded_and_returns_success(): void
    {
        $seminar = Seminar::factory()->create([
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => 10,
            'invitation_expiry_days' => 7,
            'created_by' => Account::factory()->create()->id,
        ]);

        SeminarInvitation::factory()->create([
            'seminar_id' => $seminar->id,
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
        ]);

        $exitCode = $this->artisan('training:check-for-expired-seminar-invitations')->run();

        $this->assertEquals(0, $exitCode);
    }

    #[Test]
    public function command_expires_overdue_invitations(): void
    {
        $seminar = Seminar::factory()->create([
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => 10,
            'invitation_expiry_days' => 7,
            'created_by' => Account::factory()->create()->id,
        ]);

        SeminarInvitation::factory()->create([
            'seminar_id' => $seminar->id,
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('training:check-for-expired-seminar-invitations')->run();

        $this->assertDatabaseHas('training_seminar_invitations', [
            'seminar_id' => $seminar->id,
            'status' => SeminarInvitationStatus::RemovedNoResponse->value,
        ]);
    }

    #[Test]
    public function command_does_not_expire_future_expiry_invitations(): void
    {
        $seminar = Seminar::factory()->create([
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => 10,
            'invitation_expiry_days' => 7,
            'created_by' => Account::factory()->create()->id,
        ]);

        SeminarInvitation::factory()->create([
            'seminar_id' => $seminar->id,
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->addDays(5),
        ]);

        $this->artisan('training:check-for-expired-seminar-invitations')->run();

        $this->assertDatabaseMissing('training_seminar_invitations', [
            'seminar_id' => $seminar->id,
            'status' => SeminarInvitationStatus::RemovedNoResponse->value,
        ]);
    }

    #[Test]
    public function command_handles_multiple_expired_invitations(): void
    {
        $seminar = Seminar::factory()->create([
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => 10,
            'invitation_expiry_days' => 7,
            'created_by' => Account::factory()->create()->id,
        ]);

        SeminarInvitation::factory()->count(3)->create([
            'seminar_id' => $seminar->id,
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
        ]);

        $this->artisan('training:check-for-expired-seminar-invitations')->run();

        $expiredCount = SeminarInvitation::where('seminar_id', $seminar->id)
            ->where('status', SeminarInvitationStatus::RemovedNoResponse)
            ->count();

        $this->assertEquals(3, $expiredCount);
    }

    #[Test]
    public function command_removes_from_waiting_list_when_expiring(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $student = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($student, $this->privacc);

        $seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'date' => now()->addDays(14)->format('Y-m-d'),
            'from' => '10:00',
            'to' => '16:00',
            'capacity' => 10,
            'invitation_expiry_days' => 7,
            'created_by' => $this->privacc->id,
        ]);

        SeminarInvitation::factory()->create([
            'seminar_id' => $seminar->id,
            'account_id' => $student->id,
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => SeminarInvitationStatus::Sent,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertNull($waitingListAccount->fresh()->deleted_at);

        $this->artisan('training:check-for-expired-seminar-invitations')->run();

        $this->assertNotNull($waitingListAccount->fresh()->deleted_at);
    }
}
