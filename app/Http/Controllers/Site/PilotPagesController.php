<?php

namespace App\Http\Controllers\Site;

class PilotPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('Pilots', route('site.pilots.landing'));
    }

    public function viewLanding()
    {
        $this->setTitle('Pilot Training');

        return $this->viewMake('site.pilots.landing');
    }

    public function viewRatings()
    {
        $this->setTitle('Ratings');
        $this->addBreadcrumb('Ratings', route('site.pilots.ratings'));

        return $this->viewMake('site.pilots.ratings');
    }

    public function viewBecomingAMentor()
    {
        $this->setTitle('Becoming a Mentor');
        $this->addBreadcrumb('Becoming a Mentor', route('site.pilots.mentor'));

        return $this->viewMake('site.pilots.mentor');
    }

    public function viewOceanic()
    {
        $this->setTitle('Oceanic Procedures');
        $this->addBreadcrumb('Oceanic Procedures', route('site.pilots.oceanic'));

        return $this->viewMake('site.pilots.oceanic');
    }
}
