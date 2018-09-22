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
            ->with('teamPhotos', $this->getStaffPhotos());
    }

    private function getStaffPhotos()
    {
        $teamPhotos = collect(
            [
                54 => null,
                91 => null,
                2311 => null,
                3580 => null,
                4366 => null,
                5125 => null,
                5161 => null,
                6286 => null,
            ]
        );

        $ipboard = new Ipboard();

        return $teamPhotos->map(function ($value, $key) use ($ipboard) {
            try {
                return $ipboard->getMemberById($key)->photoUrl;
            } catch (\Exception $e) {
                return;
            }
        });
    }
}
