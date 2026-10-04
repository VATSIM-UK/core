<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Event as CtsEvent;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Cts\PracticalExaminers;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CtsBookingsApiTest extends TestCase
{
    #[Test]
    public function it_does_not_duplicate_a_core_booking_imported_from_cts(): void
    {
        $date = Carbon::today()->addYears(5);
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));

        $response->assertOk();
        $bookings = collect($response->json('bookings'));

        $this->assertCount(1, $bookings->where('cts_booking_id', (int) $cts->id), 'Imported CTS booking must appear exactly once');
        $this->assertSame(1, $response->json('count'));
        $this->assertSame('core', $bookings->firstWhere('cts_booking_id', (int) $cts->id)['source']);
    }

    #[Test]
    public function it_returns_a_cts_only_booking_alongside_a_core_booking(): void
    {
        $date = Carbon::today()->addYears(5);
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $ctsMember = CtsMember::factory()->forAccount(Account::factory()->create())->create();

        // CTS-only: never imported into core.
        $cts = CtsBooking::factory()->create([
            'position' => 'EGLL_APP',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '14:00:00',
            'to' => '16:00:00',
        ]);

        $core = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $date->copy()->setHour(9),
            'ends_at' => $date->copy()->setHour(10),
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));

        $response->assertOk();
        $bookings = collect($response->json('bookings'));

        $this->assertSame(2, $response->json('count'));
        $this->assertNotNull($bookings->firstWhere('cts_booking_id', (int) $cts->id), 'CTS-only booking must appear');
        $this->assertNotNull($bookings->firstWhere('id', (int) $core->id), 'Core booking must appear');
    }

    #[Test]
    public function it_never_exposes_the_member_field(): void
    {
        $date = Carbon::today()->addYears(5);
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $date->copy()->setHour(9),
            'ends_at' => $date->copy()->setHour(10),
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));

        $response->assertOk();
        $bookings = collect($response->json('bookings'));

        $this->assertCount(1, $bookings);
        $this->assertArrayNotHasKey('member', $bookings->first());
    }

    #[Test]
    public function it_is_served_under_api_bookings(): void
    {
        $date = Carbon::today()->addYears(5);
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $date->copy()->setHour(9),
            'ends_at' => $date->copy()->setHour(10),
        ]);

        $response = $this->getJson('/api/bookings?date='.$date->toDateString());

        $response->assertOk();
        $this->assertCount(1, $response->json('bookings'));
    }

    #[Test]
    public function it_sends_the_booking_id_as_an_integer(): void
    {
        $date = Carbon::today()->addYears(5);
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $date->copy()->setHour(9),
            'ends_at' => $date->copy()->setHour(10),
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));

        $response->assertOk();
        $row = collect($response->json('bookings'))->first();

        $this->assertIsInt($row['id']);
        $this->assertSame((int) $booking->id, $row['id']);
    }

    #[Test]
    public function it_sends_from_and_to_as_iso_datetimes_without_a_separate_date_field(): void
    {
        $date = Carbon::today()->addYears(5);
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => $date->copy()->setHour(9),
            'ends_at' => $date->copy()->setHour(10),
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));

        $response->assertOk();
        $row = collect($response->json('bookings'))->first();

        $this->assertArrayNotHasKey('date', $row);
        $this->assertSame($date->copy()->setHour(9)->toIso8601String(), $row['from']);
        $this->assertSame($date->copy()->setHour(10)->toIso8601String(), $row['to']);
    }

    #[Test]
    public function it_rolls_the_to_datetime_to_the_next_day_for_overnight_bookings(): void
    {
        $date = Carbon::today()->addYears(5);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGLL_APP',
            'member_id' => CtsMember::factory()->create()->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '23:00:00',
            'to' => '01:00:00',
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));

        $response->assertOk();
        $row = collect($response->json('bookings'))->firstWhere('cts_booking_id', (int) $cts->id);

        $this->assertSame($date->copy()->setTime(23, 0)->toIso8601String(), $row['from']);
        $this->assertSame($date->copy()->addDay()->setTime(1, 0)->toIso8601String(), $row['to'], 'An overnight booking must roll "to" into the next day');
    }

    #[Test]
    public function it_sets_owner_to_the_member_for_a_standard_booking(): void
    {
        $date = Carbon::today()->addYears(5);
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::factory()->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $date->copy()->setHour(9),
            'ends_at' => $date->copy()->setHour(10),
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));
        $row = collect($response->json('bookings'))->first();

        $this->assertSame((int) $member->id, $row['owner']);
    }

    #[Test]
    public function it_sets_owner_to_the_examiner_not_the_student_for_exam_bookings(): void
    {
        $date = Carbon::today()->addYears(5);
        $student = Account::factory()->create();
        $examinerAccount = Account::factory()->create();
        $examiner = CtsMember::factory()->forAccount($examinerAccount)->create();

        $exam = ExamBooking::factory()->create([
            'student_id' => CtsMember::factory()->create()->id,
            'position_1' => 'EGKK_TWR',
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);
        PracticalExaminers::create(['examid' => $exam->id, 'senior' => $examiner->id, 'other' => null, 'trainee' => null]);

        Booking::create([
            'position_id' => null,
            'member_id' => $student->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $exam->id,
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));
        $row = collect($response->json('bookings'))->first();

        $this->assertSame((int) $examinerAccount->id, $row['owner']);
    }

    #[Test]
    public function it_sets_owner_to_the_mentor_not_the_student_for_mentoring_bookings(): void
    {
        $date = Carbon::today()->addYears(5);
        $student = Account::factory()->create();
        $mentorAccount = Account::factory()->create();
        $mentor = CtsMember::factory()->forAccount($mentorAccount)->create();

        $session = Session::factory()->create([
            'student_id' => CtsMember::factory()->create()->id,
            'mentor_id' => $mentor->id,
            'position' => 'EGLL_APP',
            'taken' => 1,
            'taken_date' => $date->toDateString(),
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => null,
            'member_id' => $student->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));
        $row = collect($response->json('bookings'))->first();

        $this->assertSame((int) $mentorAccount->id, $row['owner']);
    }

    #[Test]
    public function it_sets_owner_to_null_for_events(): void
    {
        $date = Carbon::today()->addYears(5);

        CtsEvent::factory()->create([
            'event' => 'Test event',
            'date' => $date->toDateString(),
            'from' => '18:00:00',
            'to' => '22:00:00',
            'gone' => 0,
        ]);

        $response = $this->getJson(route('api.cts.bookings', ['date' => $date->toDateString()]));
        $row = collect($response->json('bookings'))->firstWhere('type', 'EV');

        $this->assertNull($row['owner']);
    }
}
