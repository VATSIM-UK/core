<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\Booking\SyncToVatsimNet;
use App\Models\Booking;

class BookingObserver
{
    private const RELEVANT_FIELDS = [
        'position_id',
        'member_id',
        'starts_at',
        'ends_at',
        'type',
        'cts_booking_id',
    ];

    public function created(Booking $booking): void
    {
        $this->dispatch($booking);
    }

    public function updated(Booking $booking): void
    {
        if ($booking->wasChanged(self::RELEVANT_FIELDS)) {
            $this->dispatch($booking);
        }
    }

    public function deleted(Booking $booking): void
    {
        $this->dispatch($booking, deleted: true);
    }

    private function dispatch(Booking $booking, bool $deleted = false): void
    {
        if ($this->shouldSkip($booking)) {
            return;
        }

        $remoteId = $booking->vatsim_net_booking_id !== null ? (int) $booking->vatsim_net_booking_id : null;

        SyncToVatsimNet::dispatch($booking->getKey(), $deleted, $remoteId);
    }

    private function shouldSkip(Booking $booking): bool
    {
        if ((string) config('services.vatsim-net.bookings.key') === '') {
            return true;
        }

        return $booking->type === Booking::TYPE_EVENT;
    }
}
