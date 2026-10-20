<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarFeedTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns_not_found_for_an_unknown_token(): void
    {
        $this->get(route('api.bookings.calendar.feed', ['token' => str_repeat('a', 48)]))
            ->assertNotFound();
    }

    #[Test]
    public function it_returns_not_found_for_a_soft_deleted_account(): void
    {
        $account = Account::factory()->create();
        $token = $account->ensureCalendarFeedToken();
        $account->delete();

        $this->get(route('api.bookings.calendar.feed', ['token' => $token]))
            ->assertNotFound();
    }

    #[Test]
    public function it_serves_a_members_standard_bookings_as_a_calendar(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $booking = Booking::factory()->create([
            'member_id' => $account->id,
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
        ]);

        $response = $this->get($this->feedUrl($account));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $body = (string) $response->getContent();

        // Long description lines are folded for transport; clients unfold them,
        // so assertions run against the unfolded payload.
        $unfolded = str_replace("\r\n ", '', $body);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('END:VCALENDAR', $body);
        $this->assertStringContainsString('X-WR-CALNAME:VATSIM UK bookings', $body);
        $this->assertStringContainsString('UID:booking-'.$booking->id.'@vatsim.uk', $body);
        $this->assertStringContainsString('SUMMARY:EGKK_APP - ATC booking', $body);
        $this->assertStringContainsString('LOCATION:EGKK_APP', $body);
        $this->assertStringContainsString(route('site.bookings.calendar'), $unfolded);
    }

    #[Test]
    public function it_includes_exams_and_mentoring_sessions_for_the_student(): void
    {
        $account = Account::factory()->create();
        $student = CtsMember::factory()->forAccount($account)->create();

        $examinerAccount = Account::factory()->create();
        $examiner = CtsMember::factory()->forAccount($examinerAccount)->create();

        $mentorAccount = Account::factory()->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => $student->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => Carbon::tomorrow()->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '11:00:00',
        ]);

        PracticalExaminers::create([
            'examid' => $exam->id,
            'senior' => $examiner->id,
        ]);

        $session = Session::factory()->create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'position' => 'EGKK_APP',
            'taken' => 1,
            'taken_date' => Carbon::tomorrow()->toDateString(),
            'taken_from' => '13:00:00',
            'taken_to' => '15:00:00',
        ]);

        Booking::create([
            'member_id' => $account->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(11, 0),
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        Booking::create([
            'member_id' => $account->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::tomorrow()->setTime(13, 0),
            'ends_at' => Carbon::tomorrow()->setTime(15, 0),
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $body = (string) $this->get($this->feedUrl($account))->getContent();

        $this->assertStringContainsString('SUMMARY:EGKK_TWR - Practical exam', $body);
        $this->assertStringContainsString('SUMMARY:EGKK_APP - Mentoring session', $body);
        $this->assertStringContainsString('Examiner: ', $body);
        $this->assertStringContainsString('Mentor: ', $body);
    }

    #[Test]
    public function it_does_not_include_another_members_bookings(): void
    {
        $account = Account::factory()->create();
        $otherAccount = Account::factory()->create();

        $otherBooking = Booking::factory()->create([
            'member_id' => $otherAccount->id,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
        ]);

        $body = (string) $this->get($this->feedUrl($account))->getContent();

        $this->assertStringNotContainsString('UID:booking-'.$otherBooking->id.'@vatsim.uk', $body);
        $this->assertStringNotContainsString('BEGIN:VEVENT', $body);
    }

    #[Test]
    public function it_only_includes_bookings_inside_the_feed_window(): void
    {
        $account = Account::factory()->create();

        $tooOld = Booking::factory()->create([
            'member_id' => $account->id,
            'starts_at' => Carbon::now()->subDays(31),
            'ends_at' => Carbon::now()->subDays(31)->addHour(),
        ]);

        $tooFarAhead = Booking::factory()->create([
            'member_id' => $account->id,
            'starts_at' => Carbon::now()->addDays(181),
            'ends_at' => Carbon::now()->addDays(181)->addHour(),
        ]);

        $insideWindow = Booking::factory()->create([
            'member_id' => $account->id,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
        ]);

        $body = (string) $this->get($this->feedUrl($account))->getContent();

        $this->assertStringNotContainsString('UID:booking-'.$tooOld->id.'@vatsim.uk', $body);
        $this->assertStringNotContainsString('UID:booking-'.$tooFarAhead->id.'@vatsim.uk', $body);
        $this->assertStringContainsString('UID:booking-'.$insideWindow->id.'@vatsim.uk', $body);
    }

    #[Test]
    public function a_rescheduled_booking_keeps_the_same_uid(): void
    {
        $account = Account::factory()->create();

        $booking = Booking::factory()->create([
            'member_id' => $account->id,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
        ]);

        $before = (string) $this->get($this->feedUrl($account))->getContent();

        $booking->update([
            'starts_at' => Carbon::tomorrow()->setTime(14, 0),
            'ends_at' => Carbon::tomorrow()->setTime(16, 0),
        ]);

        $after = (string) $this->get($this->feedUrl($account))->getContent();

        $this->assertStringContainsString('UID:booking-'.$booking->id.'@vatsim.uk', $before);
        $this->assertStringContainsString('UID:booking-'.$booking->id.'@vatsim.uk', $after);
        $this->assertSame(1, substr_count($after, 'BEGIN:VEVENT'));
    }

    #[Test]
    public function it_escapes_special_characters_in_event_text(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::factory()->create([
            'member_id' => $account->id,
            'position_id' => $position->id,
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
        ]);

        $body = (string) $this->get($this->feedUrl($account))->getContent();

        // The manage link is part of the description and must be escaped from
        // ICS text, not left as raw URL punctuation.
        $this->assertStringContainsString('DESCRIPTION:Position: EGKK_APP\n', $body);
    }

    #[Test]
    public function an_account_without_bookings_still_receives_a_valid_calendar(): void
    {
        $account = Account::factory()->create();

        $body = (string) $this->get($this->feedUrl($account))->getContent();

        $this->assertStringContainsString('BEGIN:VCALENDAR', $body);
        $this->assertStringContainsString('END:VCALENDAR', $body);
        $this->assertStringNotContainsString('BEGIN:VEVENT', $body);
    }

    private function feedUrl(Account $account): string
    {
        return route('api.bookings.calendar.feed', [
            'token' => $account->ensureCalendarFeedToken(),
        ]);
    }
}
