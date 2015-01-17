<?php

namespace Controllers\Adm;

use Models\Mship\Account;
use Models\Statistic;
use \Session;
use \Response;
use \Redirect;
use \View;
use \Input;

class Dashboard extends \Controllers\Adm\AdmController {

    public function getIndex(){
        // All Stats
        $statistics = array();
        $statistics['members_total'] = (\Models\Mship\Account::count());
        $statistics['members_active'] = (\Models\Mship\Account::where("status", "=", 0)->count());
        $statistics['members_division'] = (\Models\Mship\Account\State::where("state", "=", \Enums\Account\State::DIVISION)->count());
        $statistics['members_nondivision'] = (\Models\Mship\Account\State::where("state", "!=", \Enums\Account\State::DIVISION)->count());
        $statistics['members_pending_update'] = (\Models\Mship\Account::where("cert_checked_at", "<=", \Carbon\Carbon::now()->subHours(24)->toDateTimeString())->count());
        $statistics['members_qualifications'] = (\Models\Mship\Account\Qualification::count());

        // API Requests
        $membershipStats = array();
        $membershipStatsKeys = ["members.division.current", "members.division.new", "members.new", "members.current"];
        $date = \Carbon\Carbon::parse("45 days ago");
        while($date->lt(\Carbon\Carbon::parse("today midnight"))) {
            $counts = array();
            foreach($membershipStatsKeys as $key){
                $counts[$key] = Statistic::getStatistic($date->toDateString(), $key);
            }
            $membershipStats[$date->toDateString()] = $counts;
            $date->addDay();
        }

        return $this->viewMake("adm.dashboard")
                    ->with("statistics", $statistics)
                    ->with("membershipStats", $membershipStats);
    }

    public function postSearch(){
        $searchQuery = Input::get("q", null);

        // Member search?
        if(is_numeric($searchQuery) && Account::find($searchQuery)){
            return Redirect::to("/adm/mship/account/".$searchQuery);
        }
    }
}
