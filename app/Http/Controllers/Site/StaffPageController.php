<?php

namespace App\Http\Controllers\Site;

use Alawrence\Ipboard\Ipboard;

class StaffPageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        $this->setTitle('Staff');
        $this->addBreadcrumb('Staff', route('site.staff'));

        return $this->viewMake('site.staff')
                    ->with('ipboard', new Ipboard());
    }
}
