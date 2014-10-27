<?php

namespace Controllers\Adm;

use Models\Statistic;
use \Session;
use \Response;
use \View;

class Dashboard extends \Controllers\Adm\AdmController {

    public function getIndex(){
        // All Stats
        $statistics = array();
        $statistics['members_total'] = (\Models\Mship\Account\Account::count());
        $statistics['members_active'] = (\Models\Mship\Account\Account::where("status", "=", 0)->count());
        $statistics['members_division'] = (\Models\Mship\Account\State::where("state", "=", \Enums\Account\State::DIVISION)->count());
        $statistics['members_nondivision'] = (\Models\Mship\Account\State::where("state", "!=", \Enums\Account\State::DIVISION)->count());
        $statistics['members_emails'] = (\Models\Mship\Account\Email::count());
        $statistics['members_qualifications'] = (\Models\Mship\Account\Qualification::count());

        // API Requests
        $membershipStats = array();
        $membershipStatsKeys = ["members.division.current", "members.division.new", "members.new", "members.current"];
        $date = \Carbon\Carbon::parse("90 days ago");
        do {
            $date->addDay();
            $counts = array();
            foreach($membershipStatsKeys as $key){
                $counts[$key] = Statistic::getStatistic($date->toDateString(), $key);
            }
            $membershipStats[$date->toDateString()] = $counts;
        } while($date->isPast());

        return $this->viewMake("adm.dashboard")
                    ->with("statistics", $statistics)
                    ->with("membershipStats", $membershipStats);
    }
}
