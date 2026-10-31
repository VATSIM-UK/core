<?php

namespace App\Http\Controllers\Site;

use App\Models\Mship\RoleStaffPosition;
use App\Services\Mship\StaffDirectoryService;

class StaffPageController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->addBreadcrumb('Staff', '#');
    }

    public function staff(StaffDirectoryService $staffDirectory)
    {
        $this->setTitle('Staff');
        $this->addBreadcrumb('Staff', route('site.staff'));

        return $this->viewMake('site.staff')
            ->with('atcTrainingDirectory', $staffDirectory->forDepartment(RoleStaffPosition::ATC_TRAINING_DEPARTMENT))
            ->with('technologyDirectory', $staffDirectory->forDepartment(RoleStaffPosition::TECHNOLOGY_DEPARTMENT));
    }
}
