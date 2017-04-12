<?php

namespace App\Http\Controllers\Webhook\Email;

use Input;
use Response;

class Mailgun extends EmailWebhookController
{
    public function anyRoute()
    {
        // Verify that this is a valid request!
        $timestamp = Input::get('timestamp');
        $token = Input::get('token');
        $ts_token = $timestamp.$token;

        $encHmac = hash_hmac('sha256', $ts_token, env('MAILGUN_SECRET'));

        if ($encHmac != Input::get('signature')) {
            return Response::make('Unauthorised', 406);
        }
        // END OF VERIFICATION

        // Get the messageID
        $this->messageId = Input::get('message-id', 'NOTHING');

        // Strip the @ symbol if present.
        $this->messageId = strpos($this->messageId, '@') ? substr($this->messageId, 0, strpos($this->messageId, '@')) : $this->messageId;

        // Try and find this queue message based on the ID.
        $this->queueEntry = Queue::whereMessageId($this->messageId)->first();

        if (!$this->queueEntry or !$this->queueEntry->exists) {
            return Response::make("Accepted, but email doesn't exist.", 200);
        }

        // Now, let's deal with the message itself.
        switch (Input::get('event')) {
            case 'delivered':
                $this->runDelivered(Input::get('message-headers'));
                break;
            case 'opened':
                $this->runOpened(Input::get('device-type', 'client-name', 'user-agent', 'client-os', 'ip', 'client-type'));
                break;
            case 'clicked':
                $this->runClicked(Input::get('device-type', 'client-name', 'user-agent', 'client-os', 'ip', 'client-type'));
                break;
            case 'complained':
                $this->runSpam(Input::get('message-headers'));
                break;
            case 'unsubscribed':
                $this->runUnsubscribed(Input::get('device-type', 'client-name', 'user-agent', 'client-os', 'ip', 'client-type'));
                break;
            case 'bounced':
                $this->runBounce(Input::get('code', 'error', 'recipient'));
                break;
            case 'dropped':
                $this->runDropped(Input::get('code', 'error', 'recipient'));
                break;
        }
    }
}
