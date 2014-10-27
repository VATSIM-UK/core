<?php

namespace Controllers\Mship;

use \Redirect;
use \Session;
use \View;
use \Models\Mship\Account\Account;

class Management extends \Controllers\BaseController {
    public function get_landing(){
        if($this->_current_account){
            return Redirect::to("/mship/auth/redirect");
        }

        return $this->viewMake("mship.management.landing");
    }
    public function get_dashboard(){
        return $this->viewMake("mship.management.dashboard");
    }
}
