<?php

namespace App\Http\Controllers\Adm\VisitTransfer;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Statistic;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Reference;
use Cache;

class Dashboard extends AdmController
{
    public function getDashboard()
    {
        $statisticsRaw = Cache::remember('visittransfer::statistics', 60 * 60, function () {
            $statistics = [];

            $statistics['applications_total'] = Application::all()->count();

            $statistics['applications_open'] = Application::status(Application::$APPLICATION_IS_CONSIDERED_OPEN)->count();

            $statistics['applications_closed'] = Application::status(Application::$APPLICATION_IS_CONSIDERED_CLOSED)->count();

            $statistics['references_pending'] = Reference::statusIn([
                Reference::STATUS_DRAFT,
                Reference::STATUS_REQUESTED,
            ])->count();

            $statistics['references_approval'] = Reference::status(Reference::STATUS_UNDER_REVIEW)->count();

            $statistics['references_accepted'] = Reference::statusIn([
                Reference::STATUS_ACCEPTED,
            ])->count();

            return $statistics;
        });

        $statisticsGraph = Cache::remember('visittransfer::statistics.graph', (60 * 24) * 60, function () {
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

        return $this->viewMake('visit-transfer.admin.dashboard')
            ->with('statisticsRaw', $statisticsRaw)
            ->with('statisticsGraph', $statisticsGraph);
    }
}
