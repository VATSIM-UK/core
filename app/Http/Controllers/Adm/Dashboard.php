<?php

namespace App\Http\Controllers\Adm;

use DB;
use Cache;
use Input;
use Redirect;
use App\Models\Statistic;
use App\Models\Mship\State;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Email as AccountEmail;

class Dashboard extends \App\Http\Controllers\Adm\AdmController
{
    public function getIndex()
    {
        $statistics = Cache::remember('statistics.mship', 60, function () {
            // All Stats
            $statistics = [];
            $statistics['members_total'] = (\App\Models\Mship\Account::count());
            $statistics['members_active'] = (\App\Models\Mship\Account::where('status', '=', 0)->count());

            $statistics['members_division'] = DB::table('mship_account_state')
                ->where('state_id', '=', State::findByCode('DIVISION')->id)
                ->count();

            $statistics['members_nondivision'] = DB::table('mship_account_state')
                ->where('state_id', '!=', State::findByCode('DIVISION')->id)
                ->count();

            $statistics['members_pending_update'] = (\App\Models\Mship\Account::where('cert_checked_at', '<=', \Carbon\Carbon::now()
                ->subDay()->toDateTimeString())->where('last_login', '>=', \Carbon\Carbon::now()
                ->subMonths(3)->toDateTimeString())->count());

            $statistics['members_qualifications'] = (DB::table('mship_account_qualification')->count());

            return $statistics;
        });

        $membershipStats = Cache::remember('statistics.membership.graph', 60 * 24, function () {
            $membershipStats = [];
            $membershipStatsKeys = ['members.division.current', 'members.division.new', 'members.new', 'members.current'];
            $date = \Carbon\Carbon::parse('45 days ago');
            while ($date->lt(\Carbon\Carbon::parse('today midnight'))) {
                $counts = [];
                foreach ($membershipStatsKeys as $key) {
                    $counts[$key] = Statistic::getStatistic($date->toDateString(), $key);
                }
                $membershipStats[$date->toDateString()] = $counts;
                $date->addDay();
            }

            return $membershipStats;
        });

        return $this->viewMake('adm.dashboard')
                        ->with('statistics', $statistics)
                        ->with('membershipStats', $membershipStats);
    }

    public function anySearch($searchQuery = null)
    {
        if ($searchQuery == null) {
            $searchQuery = Input::get('q', null);
        }

        if (strlen($searchQuery) < 2 or $searchQuery == null) {
            return Redirect::route('adm.dashboard');
        }

        // Direct member?
        if (is_numeric($searchQuery) && Account::find($searchQuery)) {
            return Redirect::route('adm.mship.account.details', [$searchQuery]);
        }

        // Global searches!
        $members = Cache::remember("adm_dashboard_membersearch_{$searchQuery}", 60, function () use ($searchQuery) {
            return Account::where('id', 'LIKE', '%'.$searchQuery.'%')
                ->orWhere(\DB::raw("CONCAT(`name_first`, ' ', `name_last`)"), 'LIKE', '%'.$searchQuery.'%')
                ->limit(25)
                ->get();
        });

        $emails = Cache::remember("adm_dashboard_emailssearch_{$searchQuery}", 60, function () use ($searchQuery) {
            return AccountEmail::where('email', 'LIKE', '%'.$searchQuery.'%')
                ->limit(25)
                ->get();
        });

        $this->setTitle('Global Search Results: '.$searchQuery);

        return $this->viewMake('adm.search')
                        ->with('members', $members)
                        ->with('emails', $emails);
    }
}
