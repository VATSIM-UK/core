<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_a_standard_booking(): void
    {
        $position = Position::factory()->create();
        $member = Account::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($position->id, $booking->position_id);
        $this->assertEquals($member->id, $booking->member_id);
        $this->assertEquals(Booking::TYPE_STANDARD, $booking->type);
    }

    #[Test]
    public function it_can_create_a_booking_without_member(): void
    {
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertNull($booking->member_id);
    }

    #[Test]
    public function overlapping_scope_detects_conflicts(): void
    {
        $position = Position::factory()->create();
        $member = Account::factory()->create();

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $overlapping = Booking::overlapping(
            Carbon::tomorrow()->setHour(11),
            Carbon::tomorrow()->setHour(13),
            $position->id
        )->exists();

        $nonOverlapping = Booking::overlapping(
            Carbon::tomorrow()->setHour(13),
            Carbon::tomorrow()->setHour(15),
            $position->id
        )->exists();

        $this->assertTrue($overlapping);
        $this->assertFalse($nonOverlapping);
    }

    #[Test]
    public function it_belongs_to_position(): void
    {
        $position = Position::factory()->create();
        $booking = Booking::factory()->create(['position_id' => $position->id]);

        $this->assertTrue($booking->position->is($position));
    }

    #[Test]
    public function it_belongs_to_member(): void
    {
        $member = Account::factory()->create();
        $booking = Booking::factory()->create(['member_id' => $member->id]);

        $this->assertTrue($booking->member->is($member));
    }

    #[Test]
    public function position_has_many_bookings(): void
    {
        $position = Position::factory()->create();
        Booking::factory()->count(3)->create(['position_id' => $position->id]);
        Booking::factory()->create(['position_id' => Position::factory()->create()->id]);

        $this->assertCount(3, $position->bookings);
    }

    #[Test]
    public function it_casts_dates(): void
    {
        $booking = Booking::factory()->create();

        $this->assertInstanceOf(Carbon::class, $booking->starts_at);
        $this->assertInstanceOf(Carbon::class, $booking->ends_at);
    }

    #[Test]
    public function scope_of_type_filters_correctly(): void
    {
        Booking::factory()->create(['type' => Booking::TYPE_STANDARD]);
        Booking::factory()->forExam()->create();

        $standards = Booking::ofType(Booking::TYPE_STANDARD)->get();
        $exams = Booking::ofType(Booking::TYPE_EXAM)->get();

        $this->assertCount(1, $standards);
        $this->assertCount(1, $exams);
    }

    #[Test]
    public function scope_live_atc_excludes_non_atc_positions(): void
    {
        $position = Position::factory()->create(['type' => Position::TYPE_TOWER]);
        Booking::factory()->create(['position_id' => $position->id]);
        Booking::factory()->create(['position_id' => null, 'type' => Booking::TYPE_EVENT]);

        $liveAtc = Booking::liveAtc()->get();

        $this->assertCount(1, $liveAtc);
    }

    #[Test]
    public function scope_not_event_excludes_event_bookings(): void
    {
        Booking::factory()->create(['type' => Booking::TYPE_STANDARD]);
        Booking::factory()->create(['type' => Booking::TYPE_EVENT]);

        $notEvents = Booking::notEvent()->get();

        $this->assertCount(1, $notEvents);
        $this->assertEquals(Booking::TYPE_STANDARD, $notEvents->first()->type);
    }

    #[Test]
    public function it_resolves_bookable_morph_to_exam_booking(): void
    {
        $account = Account::factory()->create();
        $member = Member::factory()->create([
            'id' => $account->id,
            'cid' => $account->id,
        ]);
        $examBooking = ExamBooking::factory()->create([
            'student_id' => $member->id,
            'exam' => 'TWR',
            'taken' => 1,
            'finished' => ExamBooking::NOT_FINISHED_FLAG,
            'taken_date' => '2026-08-01',
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $booking = Booking::create([
            'position_id' => null,
            'member_id' => $account->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-01 12:00:00',
            'bookable_type' => ExamBooking::class,
            'bookable_id' => $examBooking->id,
        ]);

        $this->assertInstanceOf(ExamBooking::class, $booking->bookable);
        $this->assertEquals($examBooking->id, $booking->bookable->id);
    }

    #[Test]
    public function it_resolves_bookable_morph_to_session(): void
    {
        $session = Session::factory()->create([
            'taken' => 1,
            'taken_date' => '2026-08-01',
            'taken_from' => '10:00:00',
            'taken_to' => '12:00:00',
        ]);

        $booking = Booking::create([
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-01 12:00:00',
            'bookable_type' => Session::class,
            'bookable_id' => $session->id,
        ]);

        $this->assertInstanceOf(Session::class, $booking->bookable);
        $this->assertEquals($session->id, $booking->bookable->id);
    }

    #[Test]
    public function it_resolves_bookable_morph_to_cts_booking(): void
    {
        $ctsBooking = CtsBooking::factory()->create([
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $booking = Booking::create([
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-01 12:00:00',
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $ctsBooking->id,
            'cts_booking_id' => $ctsBooking->id,
        ]);

        $this->assertInstanceOf(CtsBooking::class, $booking->bookable);
        $this->assertEquals($ctsBooking->id, $booking->bookable->id);
    }

    #[Test]
    public function it_links_to_cts_booking_via_relation(): void
    {
        $ctsBooking = CtsBooking::factory()->create([
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $booking = Booking::create([
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => '2026-08-01 10:00:00',
            'ends_at' => '2026-08-01 12:00:00',
            'cts_booking_id' => $ctsBooking->id,
        ]);

        $this->assertInstanceOf(CtsBooking::class, $booking->ctsBooking);
        $this->assertEquals($ctsBooking->id, $booking->ctsBooking->id);
    }
}
