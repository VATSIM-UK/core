<?php

namespace App\Http\Controllers\Site;

use App\Models\Mship\State;
use App\Repositories\Cts\EventRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return $this->viewMake('site.home')
            ->with('nextEvent', $this->nextEvent())
            ->with('stats', $this->stats())
            ->with('events', $this->todaysEvents());
    }

    private function nextEvent()
    {
        return Cache::remember('home.nextEvent', now()->addDay(), function () {
            return app(EventRepository::class)->getNextEvent();
        });
    }

    private function stats()
    {
        return Cache::remember('home.mship.stats', now()->addDay(), function () {
            $stat['members_division'] = DB::table('mship_account_state')
                ->leftJoin('mship_account', 'mship_account_state.account_id', '=', 'mship_account.id')
                ->where('inactive', '=', 0)
                ->whereNull('mship_account.deleted_at')
                ->where('state_id', '=', State::findByCode('DIVISION')->id)
                ->whereNull('mship_account_state.end_at')
                ->count();

            return $stat;
        });
    }

    private function todaysEvents()
    {
        return Cache::remember('home.mship.events', now()->addHour(), function () {
            $bookings = new EventRepository;

            return $bookings->getTodaysEvents();
        });
    }
}
