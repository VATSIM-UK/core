<?php

namespace App\Http\Controllers\Site;

class CommunityPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('Community', '#');
    }

    public function viewVtGuide()
    {
        $this->setTitle('Visiting & Transferring');
        $this->addBreadcrumb('Visiting & Transferring', route('site.community.vt-guide'));

        return $this->viewMake('site.community.vt-guide');
    }

    public function viewTeamspeak()
    {
        $this->setTitle('TeamSpeak');
        $this->addBreadcrumb('TeamSpeak', route('site.community.teamspeak'));

        return $this->viewMake('site.community.teamspeak');
    }

    public function viewTerms()
    {
        $this->setTitle('Terms & Conditions');
        $this->addBreadcrumb('Terms & Conditions', route('site.community.terms'));

        return $this->viewMake('site.community.terms');
    }

    public function viewPrivacy()
    {
        $this->setTitle('Privacy Policy');
        $this->addBreadcrumb('Privacy Policy', route('site.community.privacy'));

        return $this->viewMake('site.community.privacy');
    }

    public function viewDPP()
    {
        $this->setTitle('Data Protection & Handling Policy');
        $this->addBreadcrumb('Data Protection & Handling Policy', route('site.community.data-protection'));

        return $this->viewMake('site.community.data-protection');
    }
}
