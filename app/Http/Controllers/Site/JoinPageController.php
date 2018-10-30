<?php

namespace App\Http\Controllers\Site;

class JoinPageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        $this->setTitle('Join Us');
        $this->addBreadcrumb('Join Us', route('site.join'));

        return $this->viewMake('site.join');
    }
}
