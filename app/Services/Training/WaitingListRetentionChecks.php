<?php

namespace App\Services\Training;

use App\Models\Training\WaitingList\WaitingListRetentionChecks as WaitingListRetentionChecksModel;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WaitingListRetentionChecks
{
    public static function getExpiredChecks(Carbon $date): Collection
    {
        return WaitingListRetentionChecksModel::where('expires_at', '<', $date)->get();
    }
}
