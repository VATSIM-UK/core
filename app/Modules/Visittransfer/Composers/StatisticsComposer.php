<?php namespace App\Modules\Visittransfer\Composers;

use App\Modules\Visittransfer\Models\Application;
use Illuminate\View\View;

class StatisticsComposer {
    public function __construct(){

    }

    public function compose(View $view){
        $view->with("visittransfer_statistics_applications_total", Application::all()->count());
        $view->with("visittransfer_statistics_applications_open", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_OPEN)->count());
        $view->with("visittransfer_statistics_applications_closed", Application::statusIn(Application::$APPLICATION_IS_CONSIDERED_CLOSED)->count());
    }
}