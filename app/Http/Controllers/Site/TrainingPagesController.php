<?php

namespace App\Http\Controllers\Site;

class TrainingPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('ATC Training Process', '#');
    }

    public function viewS1Syllabus()
    {
        $this->setTitle('S1 Syllabus and Lesson Plans');
        $this->addBreadcrumb('S1 Syllabus and Lesson Plans', route('site.training.s1-syllabus'));

        return $this->viewMake('site.training.s1-syllabus');
    }
}
