<?php

namespace App\Http\Controllers\Site;

class StaffPageController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->addBreadcrumb('Staff', '#');
    }

    public function staff()
    {
        $this->setTitle('Staff');
        $this->addBreadcrumb('Staff', route('site.staff'));

        return $this->viewMake('site.staff');
    }
}