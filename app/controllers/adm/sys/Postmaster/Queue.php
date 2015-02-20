<?php

namespace Controllers\Adm\Sys\Postmaster;

use \Input;
use \Session;
use \Response;
use \Request;
use \Config;
use \Redirect;
use \DB;
use \Models\Sys\Postmaster\Queue as PostmasterQueueData;

class Queue extends \Controllers\Adm\AdmController {

    public function getIndex() {
        // Get all emails in the queue!
        $queue = PostmasterQueueData::orderBy("updated_at", "DESC")
                                     ->paginate(3);

        return $this->viewMake("adm.sys.postmaster.queue.index")
                    ->with("queue", $queue);
    }

}
