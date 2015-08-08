<?php

namespace Controllers;

use \Session;
use \Response;
use \View;

class Error extends \Controllers\BaseController {

    public function getDisplay($code){
        if(View::exists("error.".$code)){
            return $this->viewMake("error.".$code);
        }

        return $this->viewMake("error.default");
    }
}
