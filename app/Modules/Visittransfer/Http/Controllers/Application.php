<?php

namespace App\Modules\Visittransfer\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use Auth;

class Application extends BaseController {

    public function getStart($applicationType){
        return $this->viewMake("visittransfer::application.terms")
                    ->with("applicationType", $applicationType);
    }

    public function postStart(){

    }

}
