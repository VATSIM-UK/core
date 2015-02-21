<?php

namespace Controllers\Adm\Sys;

use \AuthException;
use \Input;
use \Session;
use \Response;
use \View;
use \VatsimSSO;
use \Config;
use \Redirect;
use \Models\Sys\Timeline\Entry;

class Timeline extends \Controllers\Adm\AdmController {

    public function getIndex(){
        $entries = Entry::orderBy("created_at", "DESC")->limit(100)->get();
        return $this->viewMake("adm.sys.timeline")->with("entries", $entries);
    }
}
