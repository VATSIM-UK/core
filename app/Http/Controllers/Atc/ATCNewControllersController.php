<?php

namespace App\Http\Controllers\Atc;

class ATCNewControllersController extends \App\Http\Controllers\BaseController
{
    public function __invoke()
    {
        return $this->viewMake('controllers.new_controllers');
    }
}
