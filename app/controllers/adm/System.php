<?php

namespace Controllers\Adm;

use \AuthException;
use \Input;
use \Session;
use \Response;
use \View;
use \VatsimSSO;
use \Config;
use \Redirect;
use \Models\Sys\Timeline\Entry;

class System extends \Controllers\Adm\AdmController {

    public function getTimeline(){
        $entries = Entry::orderBy("created_at", "DESC")->limit(100)->get();
        return $this->viewMake("adm.system.timeline")->with("entries", $entries);
    }
}
