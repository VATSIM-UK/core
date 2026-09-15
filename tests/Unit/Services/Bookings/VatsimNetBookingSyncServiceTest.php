<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use App\Services\Bookings\VatsimNetBookingSyncService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VatsimNetBookingSyncServiceTest extends TestCase
{
    private VatsimNetBookingSyncService $service;

    private Account $examiner;

    private Account $mentor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(VatsimNetBookingSyncService::class);
    }

    #[Test]
    public function it_creates_a_remote_booking_and_stores_the_id(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 999], 201)]);

        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $member = Account::factory()->create();

        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
        ]);

        $this->service->sync($booking);

        $this->assertSame(999, $booking->fresh()->vatsim_net_booking_id);

        Http::assertSent(fn ($request) => $request['callsign'] === 'EGKK_TWR'
            && $request['cid'] === $member->id
            && $request['type'] === 'booking');
    }

    #[Test]
    public function it_updates_when_a_remote_id_already_exists(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 77], 200)]);

        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'vatsim_net_booking_id' => 77,
        ]);

        $this->service->sync($booking);

        Http::assertSent(fn ($request) => $request->url() === 'https://atc-bookings.vatsim.net/api/booking/77' && $request->method() === 'PUT');
    }

    #[Test]
    public function it_resolves_the_examiner_for_exam_bookings(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 1], 201)]);

        $booking = $this->makeExamBooking();

        $this->service->sync($booking);

        Http::assertSent(fn ($request) => $request['cid'] === $this->examiner->id && $request['type'] === 'exam');
    }

    #[Test]
    public function it_resolves_the_mentor_for_mentoring_bookings(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 1], 201)]);

        $booking = $this->makeMentoringBooking();

        $this->service->sync($booking);

        Http::assertSent(fn ($request) => $request['cid'] === $this->mentor->id && $request['type'] === 'mentoring');
    }

    #[Test]
    public function it_skips_event_bookings(): void
    {
        Http::fake();

        $booking = Booking::factory()->forEvent()->create();

        $this->service->sync($booking);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_skips_virtual_positions(): void
    {
        Http::fake();

        $position = Position::factory()->create(['virtual' => true]);
        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
        ]);

        $this->service->sync($booking);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_skips_when_there_is_no_position(): void
    {
        Http::fake();

        $booking = Booking::factory()->create([
            'position_id' => null,
            'type' => Booking::TYPE_EXAM,
        ]);

        $this->service->sync($booking);

        Http::assertNothingSent();
    }

    #[Test]
    public function it_deletes_by_remote_id(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response('', 204)]);

        $this->service->delete(123);

        Http::assertSent(fn ($request) => $request->url() === 'https://atc-bookings.vatsim.net/api/booking/123' && $request->method() === 'DELETE');
    }

    #[Test]
    public function it_does_nothing_when_delete_has_no_remote_id(): void
    {
        Http::fake();

        $this->service->delete(null);

        Http::assertNothingSent();
    }

    private function makeExamBooking(): Booking
    {
        $this->examiner = Account::factory()->create();
        $student = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        $examinerMember = Member::factory()->forAccount($this->examiner)->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => $studentMember->id,
            'position_1' => 'EGKK_TWR',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
        ]);

        PracticalExaminers::create([
            'examid' => $exam->id,
            'senior' => $examinerMember->id,
            'other' => null,
            'trainee' => null,
        ]);

        return Booking::factory()->forExam()->create([
            'position_id' => $position->id,
            'member_id' => $student->id,
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);
    }

    private function makeMentoringBooking(): Booking
    {
        $this->mentor = Account::factory()->create();
        $student = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGLL_APP']);

        $mentorMember = Member::factory()->forAccount($this->mentor)->create();
        $studentMember = Member::factory()->forAccount($student)->create();

        $session = Session::factory()->create([
            'student_id' => $studentMember->id,
            'mentor_id' => $mentorMember->id,
            'position' => 'EGLL_APP',
        ]);

        return Booking::factory()->forMentoring()->create([
            'position_id' => $position->id,
            'member_id' => $student->id,
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);
    }
}
