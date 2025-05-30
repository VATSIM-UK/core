<?php

namespace App\Http\Controllers\Site;

class PolicyPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('Policy', '#');
    }

    public function viewTerms()
    {
        $this->setTitle('Terms & Conditions');
        $this->addBreadcrumb('Terms & Conditions', route('site.policy.terms'));

        return $this->viewMake('site.policy.terms');
    }

    public function viewPrivacy()
    {
        $this->setTitle('Privacy Policy');
        $this->addBreadcrumb('Privacy Policy', route('site.policy.privacy'));

        return $this->viewMake('site.policy.privacy');
    }

    public function viewDPP()
    {
        $this->setTitle('Data Protection & Handling Policy');
        $this->addBreadcrumb('Data Protection & Handling Policy', route('site.policy.data-protection'));

        return $this->viewMake('site.policy.data-protection');
    }
}
