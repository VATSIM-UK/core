<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Bookings;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Mship\Account;
use App\Services\Bookings\BookingPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingPolicyTest extends TestCase
{
    use DatabaseTransactions;

    private BookingPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = app(BookingPolicy::class);
    }

    private function makeCtsMember(Account $account): CtsMember
    {
        // forAccount deliberately sets id != cid (id = account->id + 5000000)
        return CtsMember::factory()->forAccount($account)->create();
    }

    #[Test]
    public function it_rejects_bookings_more_than_90_days_in_advance(): void
    {
        $account = Account::factory()->create();

        $this->expectException(\RuntimeException::class);

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(91),
        );
    }

    #[Test]
    public function it_rejects_when_six_advance_bookings_exist(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        foreach (range(1, 6) as $i) {
            Booking::create([
                'position_id' => $position->id,
                'member_id' => $account->id,
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => Carbon::now()->addDays($i)->setHour(10),
                'ends_at' => Carbon::now()->addDays($i)->setHour(12),
            ]);
        }

        $this->expectException(\RuntimeException::class);

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
        );
    }

    #[Test]
    public function it_allows_up_to_six_advance_bookings(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        foreach (range(1, 5) as $i) {
            Booking::create([
                'position_id' => $position->id,
                'member_id' => $account->id,
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => Carbon::now()->addDays($i)->setHour(10),
                'ends_at' => Carbon::now()->addDays($i)->setHour(12),
            ]);
        }

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_counts_cts_bookings_via_cid_not_internal_id(): void
    {
        $account = Account::factory()->create();
        $ctsMember = $this->makeCtsMember($account);
        $this->assertNotSame($account->id, $ctsMember->id);

        foreach (range(1, 6) as $i) {
            CtsBooking::factory()->create([
                'member_id' => $ctsMember->id,
                'type' => 'BK',
                'date' => Carbon::now()->addDays($i)->toDateString(),
                'from' => '10:00:00',
                'to' => '12:00:00',
            ]);
        }

        $this->expectException(\RuntimeException::class);

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
        );
    }

    #[Test]
    public function it_only_counts_standard_bookings(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();
        $ctsMember = $this->makeCtsMember($account);

        foreach (range(1, 6) as $i) {
            Booking::create([
                'position_id' => $position->id,
                'member_id' => $account->id,
                'type' => Booking::TYPE_EXAM,
                'starts_at' => Carbon::now()->addDays($i)->setHour(10),
                'ends_at' => Carbon::now()->addDays($i)->setHour(12),
            ]);
        }

        foreach (range(1, 6) as $i) {
            CtsBooking::factory()->create([
                'member_id' => $ctsMember->id,
                'type' => 'EX',
                'date' => Carbon::now()->addDays($i)->toDateString(),
                'from' => '10:00:00',
                'to' => '12:00:00',
            ]);
        }

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_does_not_double_count_imported_cts_bookings(): void
    {
        $account = Account::factory()->create();
        $ctsMember = $this->makeCtsMember($account);
        $position = Position::factory()->create();

        $cts = CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => Carbon::now()->addDays(1)->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $account->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(1)->setHour(10),
            'ends_at' => Carbon::now()->addDays(1)->setHour(12),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        // Four plain bookings plus the imported mirror (which also counts as a
        // core row) total five. If the CTS source were counted too, that would
        // reach six and throw; the pass below only holds when dedupe works.
        foreach (range(1, 4) as $i) {
            Booking::create([
                'position_id' => $position->id,
                'member_id' => $account->id,
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => Carbon::now()->addDays($i + 1)->setHour(10),
                'ends_at' => Carbon::now()->addDays($i + 1)->setHour(12),
            ]);
        }

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_counts_overnight_cts_bookings(): void
    {
        $account = Account::factory()->create();
        $ctsMember = $this->makeCtsMember($account);

        foreach (range(1, 5) as $i) {
            CtsBooking::factory()->create([
                'member_id' => $ctsMember->id,
                'type' => 'BK',
                'date' => Carbon::now()->addDays($i)->toDateString(),
                'from' => '10:00:00',
                'to' => '12:00:00',
            ]);
        }

        // Overnight booking: starts at 23:00, wraps past midnight to 01:00.
        CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => Carbon::now()->addDays(6)->toDateString(),
            'from' => '23:00:00',
            'to' => '01:00:00',
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
        );
    }

    #[Test]
    public function it_ignores_bookings_within_two_hours_when_counting(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        foreach (range(1, 6) as $i) {
            Booking::create([
                'position_id' => $position->id,
                'member_id' => $account->id,
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => Carbon::now()->addMinutes($i * 10),
                'ends_at' => Carbon::now()->addMinutes($i * 10 + 30),
            ]);
        }

        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addMinutes(90),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_excludes_the_booking_being_updated_from_the_count(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        $bookings = [];
        foreach (range(1, 6) as $i) {
            $bookings[] = Booking::create([
                'position_id' => $position->id,
                'member_id' => $account->id,
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => Carbon::now()->addDays($i)->setHour(10),
                'ends_at' => Carbon::now()->addDays($i)->setHour(12),
            ]);
        }

        // Excluding one of the six existing bookings leaves only five counted,
        // so creating a seventh is allowed.
        $this->policy->validateAdvanceBookingLimits(
            $account->id,
            Carbon::now()->addDays(7)->setHour(10),
            excludeBookingId: $bookings[0]->id,
        );

        $this->assertTrue(true);
    }
}
