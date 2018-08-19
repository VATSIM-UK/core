<?php

namespace App\Http\Controllers\Site;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return view('site.home');
    }
}
