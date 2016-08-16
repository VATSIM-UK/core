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
        $view->with("visittransfer_statistics_references_pending_approval", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_OPEN)->count());
        $view->with("visittransfer_statistics_references_pending_submission", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_CLOSED)->count());
        $view->with("visittransfer_statistics_references_submitted", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_CLOSED)->count());


    }
}