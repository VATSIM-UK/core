<?php

namespace App\Listeners\NetworkData;

use App\Events\NetworkData\AtcSessionEnded;
use App\Models\Atc\Endorsement;
use App\Notifications\AtcSessionRecordedConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class FlushEndorsementCache implements ShouldQueue
{
    public function handle(AtcSessionEnded $event)
    {
        $user = $event->atcSession->account;
        $endorsementIds = Endorsement::pluck('id');

        foreach ($endorsementIds as $endorsementId) {
            Cache::forget(Endorsement::generateCacheKey($endorsementId, $user->id));
        }
    }
}
