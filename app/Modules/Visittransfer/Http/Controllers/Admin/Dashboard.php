<?php namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Referee;
use Auth;
use Cache;

class Dashboard extends AdmController
{

    public function getDashboard()
    {
        $statisticsRaw = Cache::remember("visittransfer::statistics", 60, function () {
            $statistics = [];

            $statistics['applications_total'] = Application::all()->count();

            $statistics['applications_accepted'] = Application::statusIn([
                Application::STATUS_ACCEPTED,
                Application::STATUS_COMPLETED,
            ])->count();

            $statistics['applications_rejected'] = Application::statusIn([
                Application::STATUS_REJECTED,
                Application::STATUS_LAPSED,
            ])->count();

            $statistics['references_pending'] = Referee::statusIn([
                Referee::STATUS_DRAFT,
                Referee::STATUS_REQUESTED,
            ])->count();

            $statistics['references_approval'] = Referee::statusIn([
                Referee::STATUS_COMPLETED,
                Referee::STATUS_UNDER_REVIEW,
            ])->count();

            $statistics['references_accepted'] = Referee::statusIn([
                Referee::STATUS_ACCEPTED,
            ])->count();

            return $statistics;
        });

        $statisticsGraph = Cache::remember("visittransfer::statistics.graph", 60 * 24, function () {
            $statistics = [];
            $statisticKeys = ["applications.total", "applications.accepted", "applications.rejected", "applications.new" ];

            $date = \Carbon\Carbon::parse("180 days ago");
            while ($date->lt(\Carbon\Carbon::parse("today midnight"))) {
                $counts = [];

                foreach ($statisticKeys as $key) {
                    $counts[$key] = Statistic::getStatistic($date->toDateString(), "visittransfer::" . $key);
                }

                $statistics[$date->toDateString()] = $counts;
                $date->addDay();
            }

            return $statistics;
        });

        return $this->viewMake("visittransfer::admin.dashboard")
                    ->with("statisticsRaw", $statisticsRaw)
                    ->with("statisticsGraph", $statisticsGraph);
    }

}
