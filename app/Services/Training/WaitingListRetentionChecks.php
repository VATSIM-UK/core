<?php

namespace App\Services\Training;

use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListRetentionCheck as WaitingListRetentionCheckModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WaitingListRetentionChecks
{
    public static function getExpiredChecks(Carbon $date): Collection
    {
        return WaitingListRetentionCheckModel::where('expires_at', '<', $date)->where('status', WaitingListRetentionCheckModel::STATUS_PENDING)->get();
    }

    public static function createRetentionCheckRecord(WaitingListAccount $waitingListAccount): WaitingListRetentionCheckModel
    {
        Log::info('Creating retention check record for account: '.$waitingListAccount->account->id);

        return $waitingListAccount->retentionChecks()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionCheckModel::STATUS_PENDING,
            'token' => self::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);
    }

    public static function markRetentionCheckAsExpired(WaitingListRetentionCheckModel $retentionCheck): WaitingListRetentionCheckModel
    {
        $retentionCheck->update([
            'status' => WaitingListRetentionCheckModel::STATUS_EXPIRED,
            'removal_actioned_at' => now(),
        ]);

        return $retentionCheck->fresh();
    }

    public static function markRetentionCheckAsUsed(WaitingListRetentionCheckModel $retentionCheck): WaitingListRetentionCheckModel
    {
        $retentionCheck->update([
            'status' => WaitingListRetentionCheckModel::STATUS_USED,
            'response_at' => now(),
        ]);

        return $retentionCheck->fresh();
    }

    /**
     * Generate a unique token for the retention check.
     * Robust against any collisions on existing r
     */
    private static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (WaitingListRetentionCheckModel::where('token', $token)->exists());

        return $token;
    }
}
