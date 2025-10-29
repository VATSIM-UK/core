<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\BaseController;

class TrainingPagesController extends BaseController
{
    public function construct()
    {
        parent::construct();

        $this->addBreadCrumb('ATC Training Process', '#');
    }

    public function viewS2Syllabus()
    {
        $this->setTitle('S2 Syllabus and Lesson Plans');
        $this->addBreadCrumb('S2 Syllabus and Lesson Plans', route('site.training.s2-syllabus'));

        return $this->viewMake('site.training.s2-syllabus');
    }
}
