<?php

namespace App\Http\Controllers\Adm\Sys\Postmaster;

use Artisan;
use Input;
use Session;
use Response;
use Request;
use Config;
use Redirect;
use DB;
use App\Models\Sys\Postmaster\Queue as PostmasterQueueData;

class Queue extends \Controllers\Adm\AdmController {

    public function getIndex() {
        // Get all emails in the queue!
        $queue = PostmasterQueueData::orderBy("updated_at", "DESC")
                                    ->has("sender")
                                    ->with("sender")
                                    ->has("recipient")
                                    ->with("recipient")
                                    ->paginate(50);

        return $this->viewMake("adm.sys.postmaster.queue.index")
                    ->with("queue", $queue);
    }

    public function getView(PostmasterQueueData $postmasterQueue){
        if(!$postmasterQueue OR !$postmasterQueue->exists){
            return Redirect::route("adm.sys.postmaster.queue.index")->withError("Postmaster queue entry doesn't exist.");
        }

        $postmasterQueue->load(
            "sender", "senderEmail",
            "recipient", "recipientEmail",
            "template",
            "timelineEntriesOwner", "timelineEntriesExtra"
        );

        return $this->viewMake("adm.sys.postmaster.queue.view")
                    ->with("queue", $postmasterQueue);
    }

    public function getParse(){
        // Implement manual parsing.  To do this, we need to think carefully
        // about how to lock the rows to avoid issues.
    }

    public function getDispatch(){
        // Implement manual dispatch.  To do this, we need to think carefully
        // about how to lock the rows to avoid issues.
    }

}
