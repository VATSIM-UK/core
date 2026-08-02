<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BookingService::class);
    }

    #[Test]
    public function it_creates_a_booking(): void
    {
        $position = Position::factory()->create();

        $booking = $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function it_rejects_overlapping_booking(): void
    {
        $position = Position::factory()->create();

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(11),
            'ends_at' => Carbon::tomorrow()->setHour(13),
        ]);
    }

    #[Test]
    public function it_allows_non_overlapping_booking(): void
    {
        $position = Position::factory()->create();

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $booking = $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(13),
            'ends_at' => Carbon::tomorrow()->setHour(15),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    #[Test]
    public function it_allows_updating_without_overlap_check_when_times_unchanged(): void
    {
        $position = Position::factory()->create();
        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
        ]);

        $updated = $this->service->update($booking, ['type' => Booking::TYPE_EVENT]);

        $this->assertEquals(Booking::TYPE_EVENT, $updated->type);
    }

    #[Test]
    public function it_rejects_start_after_end(): void
    {
        $position = Position::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(12),
            'ends_at' => Carbon::tomorrow()->setHour(10),
        ]);
    }

    #[Test]
    public function is_position_available_returns_correctly(): void
    {
        $position = Position::factory()->create();

        $this->assertTrue(
            $this->service->isPositionAvailable(
                Carbon::tomorrow()->setHour(10),
                Carbon::tomorrow()->setHour(12),
                $position->id
            )
        );

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertFalse(
            $this->service->isPositionAvailable(
                Carbon::tomorrow()->setHour(11),
                Carbon::tomorrow()->setHour(13),
                $position->id
            )
        );
    }

    #[Test]
    public function it_deletes_a_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->service->delete($booking);

        $this->assertModelMissing($booking);
    }

    #[Test]
    public function it_rejects_booking_when_member_not_qualified(): void
    {
        $member = Account::factory()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 1]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_TOWER]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('not qualified');

        $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);
    }

    #[Test]
    public function it_allows_booking_when_member_is_qualified(): void
    {
        $member = Account::factory()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 3]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_TOWER]);

        $booking = $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    #[Test]
    public function it_rejects_booking_when_member_has_overlap(): void
    {
        $member = Account::factory()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('already have a booking');

        $this->service->create([
            'position_id' => Position::factory()->create()->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(11),
            'ends_at' => Carbon::tomorrow()->setHour(13),
        ]);
    }

    #[Test]
    public function it_allows_member_to_have_non_overlapping_bookings(): void
    {
        $member = Account::factory()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $booking = $this->service->create([
            'position_id' => Position::factory()->create()->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(13),
            'ends_at' => Carbon::tomorrow()->setHour(15),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    #[Test]
    public function it_skips_qualification_check_when_member_id_is_null(): void
    {
        $position = Position::factory()->create(['type' => Position::TYPE_TOWER]);

        $booking = $this->service->create([
            'position_id' => $position->id,
            'member_id' => null,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    #[Test]
    public function it_rejects_update_when_member_now_overlaps(): void
    {
        $member = Account::factory()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $positionA = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);
        $positionB = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        $bookingA = $this->service->create([
            'position_id' => $positionA->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $bookingB = $this->service->create([
            'position_id' => $positionB->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(13),
            'ends_at' => Carbon::tomorrow()->setHour(15),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->update($bookingB, [
            'starts_at' => Carbon::tomorrow()->setHour(11),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);
    }

    #[Test]
    public function it_rejects_booking_beyond_90_days(): void
    {
        // Member must be qualified for the position, otherwise validateMemberQualification
        // (which runs before the policy checks) throws first and the test passes for the wrong reason.
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);

        $position = Position::factory()->create(['type' => Position::TYPE_DELIVERY]);

        $this->expectException(\RuntimeException::class);

        $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::now()->addDays(91)->setHour(10),
            'ends_at' => Carbon::now()->addDays(91)->setHour(12),
        ]);
    }

    #[Test]
    public function it_does_not_enforce_policy_for_exam_bookings(): void
    {
        // Member must be qualified so the policy block runs; only the TYPE_STANDARD
        // guard should exempt the exam booking from the advance-limit check.
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);

        $position = Position::factory()->create(['type' => Position::TYPE_DELIVERY]);

        // A booking beyond 90 days but of type exam must not trip the advance-limit check.
        $booking = $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::now()->addDays(91)->setHour(10),
            'ends_at' => Carbon::now()->addDays(91)->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    #[Test]
    public function it_rejects_update_beyond_90_days_for_standard_booking(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);

        $position = Position::factory()->create(['type' => Position::TYPE_DELIVERY]);

        $booking = $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->update($booking, [
            'starts_at' => Carbon::now()->addDays(91)->setHour(10),
            'ends_at' => Carbon::now()->addDays(91)->setHour(12),
        ]);
    }

    #[Test]
    public function it_does_not_enforce_policy_when_updating_exam_booking(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);

        $position = Position::factory()->create(['type' => Position::TYPE_DELIVERY]);

        $booking = $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $updated = $this->service->update($booking, [
            'starts_at' => Carbon::now()->addDays(91)->setHour(10),
            'ends_at' => Carbon::now()->addDays(91)->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $updated);
    }

    #[Test]
    public function it_enforces_policy_when_non_standard_booking_becomes_standard(): void
    {
        // Member must be qualified so the policy block runs; only the type
        // flip should cause the advance-limit check to fire on update.
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);

        $position = Position::factory()->create(['type' => Position::TYPE_DELIVERY]);

        // Exam bookings are exempt from policy, so this can sit beyond the
        // advance window.
        $booking = $this->service->create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::now()->addDays(91)->setHour(10),
            'ends_at' => Carbon::now()->addDays(91)->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);

        // Flipping to standard with the same times must now trip the advance
        // limit check, even though nothing else on the booking changed.
        $this->service->update($booking, ['type' => Booking::TYPE_STANDARD]);
    }
}
