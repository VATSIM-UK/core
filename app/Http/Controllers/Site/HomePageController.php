<?php

namespace App\Http\Controllers\Site;

class HomePageController extends \App\Http\Controllers\BaseController
{
    public function view()
    {
        return view('site.home');
    }
}
