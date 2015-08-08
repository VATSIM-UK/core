<?php

namespace Controllers\Webhook\Email;

use Models\Sys\Timeline\Entry;
use Models\Sys\Postmaster\Queue;
use \Session;
use \Response;
use \Redirect;
use \View;
use \Input;

class EmailWebhookController extends \Controllers\Webhook\WebhookController {
    protected $messageId = null;
    protected $queueEntry = null;

    protected function runDelivered($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_DELIVERED;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_DELIVERED", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

    protected function runOpened($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_OPENED;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_OPENED", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

    protected function runClicked($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_CLICKED;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_CLICKED", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

    protected function runUnsubscribed($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_UNSUBSCRIBED;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_UNSUBSCRIBED", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

    protected function runSpam($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_SPAM;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_SPAM", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

    protected function runBounce($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_BOUNCED;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_BOUNCED", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

    protected function runDropped($data=[]){
        if(!$this->queueEntry OR !$this->queueEntry->exists){
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_BOUNCED;
        $this->queueEntry->save();

        // Timeline this bad boy!
        Entry::log("SYS_POSTMASTER_QUEUE_BOUNCED", $this->queueEntry->recipient, $this->queueEntry, $data);
    }

}
