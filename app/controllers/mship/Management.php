<?php

namespace Controllers\Mship;

use \Redirect;
use \Auth;
use \Session;
use \View;
use \Models\Mship\Account;

class Management extends \Controllers\BaseController {
    public function getLanding(){
        if(Auth::user()->check()){
            return Redirect::route("mship.auth.redirect");
        }

        return $this->viewMake("mship.management.landing");
    }
    public function getDashboard(){
        // Load necessary data, early!
        $this->_account->load(
            "emails",
            "qualifications", "qualifications.qualification",
            "states", "teamspeakRegistrations"
        );

        return $this->viewMake("mship.management.dashboard");
    }
}
