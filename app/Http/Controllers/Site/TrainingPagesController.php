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
}
