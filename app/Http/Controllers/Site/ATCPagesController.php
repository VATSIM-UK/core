<?php

namespace App\Http\Controllers\Site;

class ATCPagesController extends \App\Http\Controllers\BaseController
{
    public function viewLanding()
    {
        return $this->viewMake('site.atc.landing');
    }

    public function viewNewController()
    {
        return $this->viewMake('site.atc.newcontroller');
    }

    public function viewProgressionGuide()
    {
        return $this->viewMake('site.atc.progression');
    }

    public function viewEndorsements()
    {
        return $this->viewMake('site.atc.endorsements');
    }

    public function viewBecomingAMentor()
    {
        return $this->viewMake('site.atc.mentor');
    }
}