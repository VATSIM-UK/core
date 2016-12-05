<?php

namespace App\Modules\Visittransfer\Resources\Viewcomposers;

use Illuminate\View\View;
use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Models\Application;

class StatisticsComposer
{
    public function __construct()
    {
    }

    public function compose(View $view)
    {
        $view->with('visittransfer_statistics_applications_total', Application::all()->count());
        $view->with('visittransfer_statistics_applications_open', Application::open()->notStatus(Application::STATUS_IN_PROGRESS)->count());
        $view->with('visittransfer_statistics_applications_review', Application::open()->status(Application::STATUS_UNDER_REVIEW)->count());
        $view->with('visittransfer_statistics_applications_accepted', Application::open()->status(Application::STATUS_ACCEPTED)->count());
        $view->with('visittransfer_statistics_applications_closed', Application::closed()->count());

        $view->with('visittransfer_statistics_references_total', Reference::all()->count());
        $view->with('visittransfer_statistics_references_requested', Reference::requested()->count());
        $view->with('visittransfer_statistics_references_submitted', Reference::submitted()->count());
        $view->with('visittransfer_statistics_references_under_review', Reference::underReview()->count());
        $view->with('visittransfer_statistics_references_accepted', Reference::accepted()->count());
        $view->with('visittransfer_statistics_references_rejected', Reference::rejected()->count());
    }
}
