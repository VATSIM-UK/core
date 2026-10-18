<?php

namespace App\Http\Controllers\Site;

class OperationsPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('Operations', route('site.operations.landing'));
    }

    public function viewLanding()
    {
        $this->setTitle('Operations');

        return $this->viewMake('site.operations.landing');
    }
}
