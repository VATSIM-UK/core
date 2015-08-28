<?php

namespace App\Http\Controllers\Adm\Sys\Postmaster;

use Input;
use Session;
use Response;
use Request;
use Config;
use Redirect;
use DB;
use App\Models\Sys\Postmaster\Template as PostmasterTemplateData;

class Template extends \Controllers\Adm\AdmController {

    public function getIndex() {
        // Get all emails in the queue!
        $templates = PostmasterTemplateData::orderBy("updated_at", "DESC")
                                           ->paginate(50);

        return $this->viewMake("adm.sys.postmaster.template.index")
                        ->with("templates", $templates);
    }

}
