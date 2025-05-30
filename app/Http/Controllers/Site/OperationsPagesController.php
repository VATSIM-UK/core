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

    public function viewSectors()
    {
        $this->setTitle('Area Sectors');
        $this->addBreadcrumb('Area Sectors', route('site.operations.sectors'));

        return $this->viewMake('site.operations.sectors');
    }

}
