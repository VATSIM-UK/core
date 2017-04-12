<?php

namespace App\Http\Controllers\Webhook\Email;

class EmailWebhookController extends \App\Http\Controllers\Webhook\WebhookController
{
    protected $messageId = null;
    protected $queueEntry = null;

    protected function runDelivered($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_DELIVERED;
        $this->queueEntry->save();

        // TODO: LOG.
    }

    protected function runOpened($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_OPENED;
        $this->queueEntry->save();

        // TODO: Log
    }

    protected function runClicked($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_CLICKED;
        $this->queueEntry->save();

        // TODO: Log
    }

    protected function runUnsubscribed($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_UNSUBSCRIBED;
        $this->queueEntry->save();

        // TODO: LOG
    }

    protected function runSpam($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_SPAM;
        $this->queueEntry->save();

        // TODO: LOG
    }

    protected function runBounce($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_BOUNCED;
        $this->queueEntry->save();

        // TODO: LOG
    }

    protected function runDropped($data = [])
    {
        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return;
        }

        // We've got the queue item, we can now change the status to delivered.
        $this->queueEntry->status = Queue::STATUS_BOUNCED;
        $this->queueEntry->save();

        // TODO: LOG
    }
}
