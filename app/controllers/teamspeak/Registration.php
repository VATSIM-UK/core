<?php

namespace Controllers\Teamspeak;

use \Redirect;
use \Auth;
use \Session;
use \View;
use \Models\Mship\Account;

class Registration extends \Controllers\BaseController {

    public function getNew() {
        $this->_pageTitle = "New Registration";
        return $this->viewMake("teamspeak.new");
    }

    public function postCreate($uuid) {

    }

    public function postDelete($uuid) {

    }
}