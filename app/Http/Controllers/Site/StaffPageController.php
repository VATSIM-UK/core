<?php

namespace App\Http\Controllers\Site;

use Alawrence\Ipboard\Ipboard;
use Illuminate\Support\Facades\Cache;

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
                1 => null,
                2308 => null,
                5125 => null,
                6102 => null,
                7072 => null,
                7103 => null,
                7203 => null,
                7358 => null,
            ]
        );

        $ipboard = new Ipboard;

        return $teamPhotos->map(function ($value, $key) use ($ipboard) {
            try {
                if (! Cache::has($key)) {
                    Cache::put($key, $ipboard->getMemberById($key)->photoUrl, now()->addHours(24)->diffInMinutes() * 60);
                }

                return Cache::get($key);
            } catch (\Exception $e) {
                return;
            }
        });
    }
}
