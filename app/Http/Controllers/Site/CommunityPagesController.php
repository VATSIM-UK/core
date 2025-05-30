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

}
