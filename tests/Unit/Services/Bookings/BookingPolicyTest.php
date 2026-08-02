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

    #[Test]
    public function it_rejects_a_third_gatwick_ground_or_delivery_booking(): void
    {
        $account = Account::factory()->create();
        Position::factory()->create(['callsign' => 'EGKK_GND']);
        Position::factory()->create(['callsign' => 'EGKK_DEL']);

        Booking::create([
            'position_id' => Position::where('callsign', 'EGKK_GND')->first()->id,
            'member_id' => $account->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(1)->setHour(10),
            'ends_at' => Carbon::now()->addDays(1)->setHour(12),
        ]);

        Booking::create([
            'position_id' => Position::where('callsign', 'EGKK_DEL')->first()->id,
            'member_id' => $account->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(2)->setHour(10),
            'ends_at' => Carbon::now()->addDays(2)->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateGatwickLimit($account->id);
    }

    #[Test]
    public function it_allows_two_gatwick_bookings(): void
    {
        $account = Account::factory()->create();
        Position::factory()->create(['callsign' => 'EGKK_GND']);
        Position::factory()->create(['callsign' => 'EGKK_DEL']);

        Booking::create([
            'position_id' => Position::where('callsign', 'EGKK_GND')->first()->id,
            'member_id' => $account->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(1)->setHour(10),
            'ends_at' => Carbon::now()->addDays(1)->setHour(12),
        ]);

        $this->policy->validateGatwickLimit($account->id);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_counts_gatwick_split_suffixes(): void
    {
        $account = Account::factory()->create();
        Position::factory()->create(['callsign' => 'EGKK_GND_1']);
        Position::factory()->create(['callsign' => 'EGKK_DEL_1']);

        Booking::create([
            'position_id' => Position::where('callsign', 'EGKK_GND_1')->first()->id,
            'member_id' => $account->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(1)->setHour(10),
            'ends_at' => Carbon::now()->addDays(1)->setHour(12),
        ]);

        Booking::create([
            'position_id' => Position::where('callsign', 'EGKK_DEL_1')->first()->id,
            'member_id' => $account->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(2)->setHour(10),
            'ends_at' => Carbon::now()->addDays(2)->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateGatwickLimit($account->id);
    }

    #[Test]
    public function it_counts_gatwick_cts_bookings_via_cid(): void
    {
        $account = Account::factory()->create();
        $ctsMember = $this->makeCtsMember($account);

        CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'position' => 'EGKK_GND',
            'date' => Carbon::now()->addDays(1)->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        CtsBooking::factory()->create([
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'position' => 'EGKK_DEL',
            'date' => Carbon::now()->addDays(2)->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateGatwickLimit($account->id);
    }

    #[Test]
    public function it_rejects_a_booking_less_than_two_hours_in_advance(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(config('bookings.min_advance_hours').' hours in advance');

        $this->policy->validateMinimumNotice(
            $account->id,
            $position->id,
            Carbon::now()->addHour(),
        );
    }

    #[Test]
    public function it_allows_a_booking_within_two_hours_when_controlling_the_position(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        factory(\App\Models\NetworkData\Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'connected_at' => Carbon::now()->subHour(),
            'disconnected_at' => null,
        ]);

        $this->policy->validateMinimumNotice(
            $account->id,
            $position->id,
            Carbon::now()->addMinutes(30),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_an_extend_within_two_hours_when_controlling_a_different_position(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);
        Position::factory()->create(['callsign' => 'EGKK_APP']);

        factory(\App\Models\NetworkData\Atc::class)->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_APP',
            'connected_at' => Carbon::now()->subHour(),
            'disconnected_at' => null,
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateMinimumNotice(
            $account->id,
            $position->id,
            Carbon::now()->addMinutes(30),
        );
    }

    #[Test]
    public function it_rejects_an_extend_within_two_hours_when_session_has_ended(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        factory(\App\Models\NetworkData\Atc::class)->states('offline')->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'connected_at' => Carbon::now()->subHours(3),
            'disconnected_at' => Carbon::now()->subHours(2),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateMinimumNotice(
            $account->id,
            $position->id,
            Carbon::now()->addMinutes(30),
        );
    }

    #[Test]
    public function it_allows_a_booking_two_or_more_hours_in_advance_without_controlling(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_TWR']);

        $this->policy->validateMinimumNotice(
            $account->id,
            $position->id,
            Carbon::now()->addHours(3),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_when_endorsement_expires_before_booked_time(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        $group = \App\Models\Atc\PositionGroup::factory()->create();
        $group->positions()->attach($position);

        Account\Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_id' => $group->id,
            'endorsable_type' => \App\Models\Atc\PositionGroup::class,
            'expires_at' => Carbon::now()->addDays(3),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->policy->validateFutureQualification(
            $account->id,
            $position->id,
            Carbon::now()->addDays(10),
        );
    }

    #[Test]
    public function it_allows_when_endorsement_expires_after_booked_time(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        $group = \App\Models\Atc\PositionGroup::factory()->create();
        $group->positions()->attach($position);

        Account\Endorsement::factory()->create([
            'account_id' => $account->id,
            'endorsable_id' => $group->id,
            'endorsable_type' => \App\Models\Atc\PositionGroup::class,
            'expires_at' => Carbon::now()->addDays(30),
        ]);

        $this->policy->validateFutureQualification(
            $account->id,
            $position->id,
            Carbon::now()->addDays(10),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_allows_positions_with_no_position_groups(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        $this->policy->validateFutureQualification(
            $account->id,
            $position->id,
            Carbon::now()->addDay(),
        );

        $this->assertTrue(true);
    }

    #[Test]
    public function it_rejects_when_member_has_no_endorsement_for_the_group(): void
    {
        $account = Account::factory()->create();
        $position = Position::factory()->create();

        $group = \App\Models\Atc\PositionGroup::factory()->create();
        $group->positions()->attach($position);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('do not have a valid endorsement');

        $this->policy->validateFutureQualification(
            $account->id,
            $position->id,
            Carbon::now()->addDay(),
        );
    }
}
