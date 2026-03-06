<?php

namespace App\Services\Site;

use App\Models\Mship\State;
use App\Repositories\Cts\EventRepository;
use DOMDocument;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class HomePageService
{
    public function nextEvent(): string|bool
    {
        return Cache::remember('home.nextEvent', now()->addDay(), function () {
            $response = Http::get('https://cts.vatsim.uk/extras/next_event.php');

            if ($response->failed()) {
                return '';
            }

            return $this->getHTMLByID('next', $response);
        });
    }

    /**
     * @return array{members_division: int}
     */
    public function stats(): array
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

    public function todaysEvents(): mixed
    {
        return Cache::remember('home.mship.events', now()->addHour(), function () {
            $bookings = new EventRepository;

            return $bookings->getTodaysEvents();
        });
    }

    private function getHTMLByID(string $id, mixed $html): string|bool
    {
        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        $node = $dom->getElementById($id);
        if ($node) {
            return $dom->saveXML($node);
        }

        return false;
    }
}
