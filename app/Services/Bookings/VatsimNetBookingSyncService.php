<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Libraries\VatsimNetBookings;
use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Session;

class VatsimNetBookingSyncService
{
    public function __construct(
        private readonly VatsimNetBookings $bookings,
    ) {}

    public function sync(Booking $booking): void
    {
        $payload = $this->payload($booking);

        if ($payload === null) {
            return;
        }

        if ($booking->vatsim_net_booking_id !== null) {
            $this->bookings->update((int) $booking->vatsim_net_booking_id, $payload);

            return;
        }

        $remoteId = $this->bookings->create($payload);

        $booking->updateQuietly(['vatsim_net_booking_id' => $remoteId]);
    }

    public function delete(?int $remoteId): void
    {
        if ($remoteId === null) {
            return;
        }

        $this->bookings->delete($remoteId);
    }

    private function payload(Booking $booking): ?array
    {
        if ($booking->type === Booking::TYPE_EVENT) {
            return null;
        }

        $position = $booking->position;

        if ($position === null || $position->isVirtual()) {
            return null;
        }

        $cid = $this->resolveControllerCid($booking);

        if ($cid === null) {
            return null;
        }

        return [
            'callsign' => $booking->ctsBooking?->position ?? $position->callsign,
            'cid' => $cid,
            'type' => $this->mapType($booking->type),
            'start' => $booking->starts_at->format('Y-m-d H:i:s'),
            'end' => $booking->ends_at->format('Y-m-d H:i:s'),
        ];
    }

    private function resolveControllerCid(Booking $booking): ?int
    {
        return match ($booking->type) {
            Booking::TYPE_STANDARD => $booking->member_id !== null ? (int) $booking->member_id : null,
            Booking::TYPE_EXAM => $this->resolveExamCid($booking),
            Booking::TYPE_MENTORING => $this->resolveMentoringCid($booking),
            default => null,
        };
    }

    private function resolveExamCid(Booking $booking): ?int
    {
        $exam = $booking->bookable;

        if (! $exam instanceof ExamBooking) {
            return null;
        }

        $account = $exam->loadMissing('examiners.primaryExaminer')->examiners?->primaryExaminer?->account;

        return $account?->id;
    }

    private function resolveMentoringCid(Booking $booking): ?int
    {
        $session = $booking->bookable;

        if (! $session instanceof Session) {
            return null;
        }

        $account = $session->loadMissing('mentor')->mentor?->account;

        return $account?->id;
    }

    private function mapType(string $type): string
    {
        return match ($type) {
            Booking::TYPE_STANDARD => 'booking',
            Booking::TYPE_EXAM => 'exam',
            Booking::TYPE_MENTORING => 'mentoring',
            default => 'booking',
        };
    }
}
