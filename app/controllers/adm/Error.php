<?php

namespace Controllers\Adm;

use \Session;
use \Response;
use \View;

class Error extends \Controllers\Adm\AdmController {

    public function getDisplay($code){
        if(View::exists("adm.error.".$code)){
            return $this->viewMake("adm.error.".$code);
        }

        return $this->viewMake("adm.error.default");
    }
}
