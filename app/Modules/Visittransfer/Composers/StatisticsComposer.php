<?php namespace App\Modules\Visittransfer\Composers;

use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Illuminate\View\View;

class StatisticsComposer {
    public function __construct(){

    }

    public function compose(View $view){
        $view->with("visittransfer_statistics_applications_total", Application::all()->count());
        $view->with("visittransfer_statistics_applications_open", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_OPEN)->count());
        $view->with("visittransfer_statistics_applications_closed", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_CLOSED)->count());


        $view->with("visittransfer_statistics_references_total", Reference::all()->count());
        $view->with("visittransfer_statistics_references_requested", Reference::requested()->count());
        $view->with("visittransfer_statistics_references_submitted", Reference::submitted()->count());
        $view->with("visittransfer_statistics_references_under_review", Reference::underReview()->count());
        $view->with("visittransfer_statistics_references_accepted", Reference::accepted()->count());
        $view->with("visittransfer_statistics_references_rejected", Reference::rejected()->count());


    }
}