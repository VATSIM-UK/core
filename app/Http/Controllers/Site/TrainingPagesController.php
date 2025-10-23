<?php

namespace App\Http\Controllers\Site;

class TrainingPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('ATC Training Process')
    }

    publci finction viewS1Syllabus()
    {
        $this->setTitle('ATC Training Process');
        $this->addBreadcrumb('ATC Training Process', route('site.training.s1-syllabus'));

        return $this->viewMake('site.training.s1-syllabus');
    }
}