<?php

namespace App\Http\Controllers\Site;

use App\Services\Site\HomePageService;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function __construct(private HomePageService $homePageService)
    {
        parent::__construct();
    }

    public function __invoke()
    {
        return $this->viewMake('site.home')
            ->with('nextEvent', $this->homePageService->nextEvent())
            ->with('stats', $this->homePageService->stats())
            ->with('events', $this->homePageService->todaysEvents());
    }
}
