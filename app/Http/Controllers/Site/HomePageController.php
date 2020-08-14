<?php

namespace App\Http\Controllers\Site;

use App\Models\Mship\State as State;
use App\Repositories\Cts\BookingRepository;
use App\Repositories\Cts\EventRepository;
use Illuminate\Support\Facades\Cache as Cache;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Http;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return $this->viewMake('site.home')
            ->with('nextEvent', $this->nextEvent())
            ->with('stats', $this->stats())
            ->with('bookings', $this->todaysLiveAtcBookings())
            ->with('events', $this->todaysEvents());
    }

    private function nextEvent()
    {
        $response = Http::get('https://cts.vatsim.uk/extras/next_event.php');

        return $response->failed() ? '' : $this->getHTMLByID('next', $response);
    }

    public function getHTMLByID($id, $html)
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $node = $dom->getElementById($id);
        if ($node) {
            return $dom->saveXML($node);
        }

        return false;
    }

    private function stats()
    {
        $divisionMembers = Cache::remember('home.mship.stats', 1440 * 60, function () {
            $stat['members_division'] = DB::table('mship_account_state')
                ->leftJoin('mship_account', 'mship_account_state.account_id', '=', 'mship_account.id')
                ->where('inactive', '=', 0)
                ->whereNull('mship_account.deleted_at')
                ->where('state_id', '=', State::findByCode('DIVISION')->id)
                ->whereNull('mship_account_state.end_at')
                ->count();

            return $stat;
        });

        return $divisionMembers;
    }

    private function todaysLiveAtcBookings()
    {
        $bookings = new BookingRepository();

        return $bookings->getTodaysLiveAtcBookingsWithoutEvents();
    }

    private function todaysEvents()
    {
        $bookings = new EventRepository();

        return $bookings->getTodaysEvents();
    }
}
