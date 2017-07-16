<?php

namespace App\Http\Controllers\NetworkData;

use App\Http\Controllers\Adm\AdmController;
use App\Models\NetworkData\Atc;
use App\Models\Statistic;
use Cache;

class Dashboard extends AdmController
{
    public function getDashboard()
    {
        $statisticsRaw = Cache::remember('networkdata::statistics', 60, function () {
            $statistics = [];

            $statistics['atc_sessions_total'] = Atc::thisYear()->count();
            $statistics['atc_sessions_hours'] = Atc::thisYear()->count();

            return $statistics;
        });

        $statisticsGraph = Cache::remember('networkdata::statistics.graph', 60 * 24, function () {
            $statistics = [];
            $statisticKeys = ['applications.total', 'applications.open', 'applications.closed', 'applications.new'];

            $date = \Carbon\Carbon::parse('180 days ago');
            while ($date->lt(\Carbon\Carbon::parse('today midnight'))) {
                $counts = [];

                foreach ($statisticKeys as $key) {
                    $counts[$key] = Statistic::getStatistic($date->toDateString(), 'visittransfer::'.$key);
                }

                $statistics[$date->toDateString()] = $counts;
                $date->addDay();
            }

            return $statistics;
        });

        return $this->viewMake('network-data.admin.dashboard')
                    ->with('statisticsRaw', $statisticsRaw)
                    ->with('statisticsGraph', $statisticsGraph);
    }
}
