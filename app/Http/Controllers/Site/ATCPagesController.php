<?php

namespace App\Http\Controllers\Site;

class ATCPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('ATC', route('site.atc.landing'));
    }

    public function viewLanding()
    {
        $this->setTitle('ATC Training');

        return $this->viewMake('site.atc.landing');
    }

    public function viewNewController()
    {
        $this->setTitle('New Controller');
        $this->addBreadcrumb('New Controller', route('site.atc.newController'));

        return $this->viewMake('site.atc.newcontroller');
    }

    public function viewEndorsements()
    {
        $this->setTitle('ATC Endorsements');
        $this->addBreadcrumb('Endorsements', route('site.atc.endorsements'));

        return $this->viewMake('site.atc.endorsements');
    }

    public function viewHeathrow()
    {
        $this->setTitle('Heathrow Endorsements');
        $this->addBreadcrumb('Heathrow Endorsements', route('site.atc.heathrow'));

        return $this->viewMake('site.atc.heathrow');
    }

    public function viewBecomingAMentor()
    {
        $this->setTitle('Becoming a Mentor');
        $this->addBreadcrumb('Becoming a Mentor', route('site.atc.mentor'));

        return $this->viewMake('site.atc.mentor');
    }

    public function viewBookings()
    {
        $this->setTitle('Bookings');
        $this->addBreadcrumb('Bookings', route('site.atc.bookings'));

        return $this->viewMake('site.atc.bookings');
    }
}
