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
        Endorsement::pluck('id')->each(function ($id) use ($user){
            Cache::forget(Endorsement::generateCacheKey($id, $user->id));
        });
    }
}
