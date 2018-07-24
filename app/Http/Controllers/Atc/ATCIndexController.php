<?php

namespace App\Http\Controllers\Atc;

class ATCIndexController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return $this->viewMake('controllers.landing');
    }
}
