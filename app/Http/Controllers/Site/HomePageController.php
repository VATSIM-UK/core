<?php

namespace App\Http\Controllers\Site;

use App\Models\Mship\State as State;
use Illuminate\Support\Facades\Cache as Cache;
use Illuminate\Support\Facades\DB as DB;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return $this->viewMake('site.home')
            ->with('stats', $this->stats());
    }

    private function stats()
    {
        $divisionMembers = Cache::remember('home.mship.stats', 1440, function () {
            $stat['members_division'] = DB::table('mship_account_state')
                ->leftJoin('mship_account', 'mship_account_state.account_id', '=', 'mship_account.id')
                ->where('inactive', '=', 0)
                ->whereNull('mship_account.deleted_at')
                ->where('state_id', '=', State::findByCode('DIVISION')->id)
                ->count();

            return $stat;
        });

        return $divisionMembers;
    }
}
