<?php

namespace App\Observers;

use App\Jobs\Mship\SyncToDiscord;
use App\Models\Mship\Account;
use App\Models\Roster;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class RosterObserver implements ShouldHandleEventsAfterCommit
{
    public function created(Roster $roster): void
    {
        $this->syncAccountToDiscord($roster->account);
    }

    public function deleted(Roster $roster): void
    {
        $account = $roster->account ?? Account::find($roster->account_id);

        $this->syncAccountToDiscord($account);
    }

    private function syncAccountToDiscord(?Account $account): void
    {
        if ($account?->discord_id) {
            SyncToDiscord::dispatch($account);
        }
    }
}
