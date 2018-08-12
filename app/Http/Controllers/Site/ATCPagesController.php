<?php

namespace App\Http\Controllers\Site;

class ATCPagesController extends \App\Http\Controllers\BaseController
{
    public function viewLanding()
    {
        $this->setTitle('ATC Training');
        return $this->viewMake('site.atc.landing');
    }

    public function viewNewController()
    {
        $this->setTitle('New Controller');
        return $this->viewMake('site.atc.newcontroller');
    }

    public function viewProgressionGuide()
    {
        $this->setTitle('ATC Progression Guide');
        return $this->viewMake('site.atc.progression');
    }

    public function viewEndorsements()
    {
        $this->setTitle('ATC Endorsements');
        return $this->viewMake('site.atc.endorsements');
    }

    public function viewBecomingAMentor()
    {
        $this->setTitle('Becoming a Mentor');
        return $this->viewMake('site.atc.mentor');
    }
}
