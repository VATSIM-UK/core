<?php

namespace App\Http\Controllers\Site;

class PolicyPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('Policy', '#');
    }

    public function viewDivision()
    {
        $this->setTitle('Division Policy');
        $this->addBreadcrumb('Division Policy', route('site.policy.division'));

        return $this->viewMake('site.policy.division');
    }

    public function viewATCTraining()
    {
        $this->setTitle('ATC Training Policy');
        $this->addBreadcrumb('ATC Training Policy', route('site.policy.atc-training'));

        return $this->viewMake('site.policy.atc-training');
    }

    public function viewVisitTransfer()
    {
        $this->setTitle('Visiting and Transferring Policy');
        $this->addBreadcrumb('Visiting and Transferring Policy', route('site.policy.visiting-and-transferring'));

        return $this->viewMake('site.policy.visiting-and-transferring');
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

    public function viewBranding()
    {
        $this->setTitle('Branding Guidelines');
        $this->addBreadcrumb('Branding Guidelines', route('site.policy.branding'));

        return $this->viewMake('site.policy.branding');
    }

    public function viewStreaming()
    {
        $this->setTitle('Streaming Guidelines');
        $this->addBreadcrumb('Streaming Guidelines', route('site.policy.streaming'));

        return $this->viewMake('site.policy.streaming');
    }
}
