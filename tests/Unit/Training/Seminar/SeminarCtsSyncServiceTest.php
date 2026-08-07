<?php

declare(strict_types=1);

namespace Tests\Unit\Training\Seminar;

use App\Models\Cts\GroupSessionStudent;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use App\Models\Training\Seminar\Seminar;
use App\Models\Training\Seminar\SeminarAttendee;
use App\Models\Training\WaitingList;
use App\Services\Training\SeminarCtsSyncService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeminarCtsSyncServiceTest extends TestCase
{
    use DatabaseTransactions;

    private SeminarCtsSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SeminarCtsSyncService;
        Event::fake();
    }

    #[Test]
    public function sync_seminar_creates_new_cts_group_session_when_no_cts_id(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
            'cts_group_session_id' => null,
        ]);

        $member = Member::factory()->create(['cid' => $this->privacc->id]);

        $this->service->syncSeminar($seminar);

        $this->assertDatabaseHas('group_sessions', [
            'name' => $seminar->name,
            'description' => mb_substr(($seminar->description ?? $seminar->name), 0, 60),
            'date' => $seminar->date->format('Y-m-d'),
            'from' => $seminar->from,
            'to' => $seminar->to,
            'leader_id' => $member->id,
            'max_students' => $seminar->capacity,
        ], 'cts');
    }

    #[Test]
    public function sync_seminar_updates_existing_cts_group_session_when_cts_id_present(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->withCtsSession()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
            'name' => 'Original Name',
        ]);

        Member::factory()->create(['cid' => $this->privacc->id]);

        $seminar->update(['name' => 'Updated Name']);
        $seminar->refresh();

        $this->service->syncSeminar($seminar);

        $this->assertDatabaseHas('group_sessions', [
            'group_session_id' => $seminar->cts_group_session_id,
            'name' => 'Updated Name',
        ], 'cts');
    }

    #[Test]
    public function sync_seminar_stores_cts_group_session_id_back_on_seminar(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
            'cts_group_session_id' => null,
        ]);

        Member::factory()->create(['cid' => $this->privacc->id]);

        $this->service->syncSeminar($seminar);

        $seminar->refresh();
        $this->assertNotNull($seminar->cts_group_session_id);
    }

    #[Test]
    public function sync_seminar_uses_leader_member_id_from_cts_member_table(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
            'cts_group_session_id' => null,
        ]);

        Member::factory()->create(['cid' => $this->privacc->id, 'id' => 999]);

        $this->service->syncSeminar($seminar);

        $this->assertDatabaseHas('group_sessions', [
            'leader_id' => 999,
        ], 'cts');
    }

    #[Test]
    public function sync_attendee_creates_group_session_student(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->withCtsSession()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
        ]);

        $account = Account::factory()->create();
        $member = Member::factory()->create(['cid' => $account->id]);

        $attendee = SeminarAttendee::factory()->create([
            'seminar_id' => $seminar->id,
            'account_id' => $account->id,
        ]);

        $this->service->syncAttendee($attendee);

        $this->assertDatabaseHas('group_sessions_students', [
            'group_session_id' => $seminar->cts_group_session_id,
            'member_id' => $member->id,
        ], 'cts');
    }

    #[Test]
    public function sync_attendee_stores_cts_student_id_back_on_attendee(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->withCtsSession()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
        ]);

        $account = Account::factory()->create();
        Member::factory()->create(['cid' => $account->id]);

        $attendee = SeminarAttendee::factory()->create([
            'seminar_id' => $seminar->id,
            'account_id' => $account->id,
        ]);

        $this->service->syncAttendee($attendee);

        $attendee->refresh();
        $this->assertNotNull($attendee->cts_group_sessions_student_id);
    }

    #[Test]
    public function sync_attendee_does_not_duplicate_group_session_student(): void
    {
        $waitingList = WaitingList::factory()->create(['department' => 'atc']);
        $seminar = Seminar::factory()->withCtsSession()->create([
            'waiting_list_id' => $waitingList->id,
            'created_by' => $this->privacc->id,
        ]);

        $account = Account::factory()->create();
        $member = Member::factory()->create(['cid' => $account->id]);

        $attendee = SeminarAttendee::factory()->create([
            'seminar_id' => $seminar->id,
            'account_id' => $account->id,
        ]);

        $this->service->syncAttendee($attendee);
        $this->service->syncAttendee($attendee->fresh());

        $students = GroupSessionStudent::where('group_session_id', $seminar->cts_group_session_id)
            ->where('member_id', $member->id)
            ->get();

        $this->assertCount(1, $students);
    }
}
