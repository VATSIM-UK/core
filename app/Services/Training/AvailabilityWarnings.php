<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityWarnings
{
    public static function getExpiredPendingWarnings(Carbon $date): Collection
    {
        return AvailabilityWarning::where('status', 'pending')
            ->where('expires_at', '<', $date)
            ->get();
    }

    public static function markWarningAsExpired(AvailabilityWarning $warning): AvailabilityWarning
    {
        $warning->update([
            'status' => 'expired',
            'removal_actioned_at' => now(),
        ]);

        return $warning->fresh();
    }

    public static function markWarningAsResolved(AvailabilityWarning $warning, string $resolvedAvailabilityCheckId): AvailabilityWarning
    {
        $warning->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_availability_check_id' => $resolvedAvailabilityCheckId,
        ]);

        return $warning->fresh();
    }
}
