<?php

declare(strict_types=1);

namespace App\Jobs\Booking;

use App\Jobs\Concerns\LogsJobFailure;
use App\Jobs\Job;
use App\Models\Booking;
use App\Services\Bookings\VatsimNetBookingSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncToVatsimNet extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, LogsJobFailure, SerializesModels;

    public function __construct(
        private readonly int $bookingId,
        private readonly bool $deleted = false,
        private readonly ?int $remoteId = null,
    ) {}

    public function handle(VatsimNetBookingSyncService $service): void
    {
        if ($this->deleted) {
            $service->delete($this->remoteId);

            return;
        }

        $booking = Booking::find($this->bookingId);

        if ($booking === null) {
            return;
        }

        $service->sync($booking);
    }

    protected function logJobContext(): array
    {
        return [
            'booking_id' => $this->bookingId,
            'deleted' => $this->deleted,
        ];
    }
}
