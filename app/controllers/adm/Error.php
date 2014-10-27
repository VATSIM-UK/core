<?php

namespace Controllers\Adm;

use \Session;
use \Response;
use \View;

class Error extends \Controllers\Adm\AdmController {

    public function getUnauthorized(){
        return View::make("adm.error.401");
    }
}
    