<?php

namespace Controllers\Mship;

use \Redirect;
use \Session;
use \View;
use \Models\Mship\Account;

class Management extends \Controllers\BaseController {
    public function getLanding(){
        if(isset($this->_current_account->exists) && $this->_current_account){
            return Redirect::route("mship.auth.redirect");
        }

        return $this->viewMake("mship.management.landing");
    }
    public function getDashboard(){
        return $this->viewMake("mship.management.dashboard");
    }
}
