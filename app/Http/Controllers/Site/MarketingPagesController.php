<?php

namespace App\Http\Controllers\Site;

class MarketingPagesController extends \App\Http\Controllers\BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->addBreadcrumb('Marketing', '#');
    }

    public function viewBranding()
    {
        $this->setTitle('Branding Guidelines');
        $this->addBreadcrumb('Branding Guidelines', route('site.marketing.branding'));

        return $this->viewMake('site.marketing.branding');
    }
}
