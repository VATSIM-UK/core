<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Models\Email\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SendGrid extends WebhookController
{
    /**
     * Parameters from SendGrid to exclude from the event data field.
     *
     * @var array
     */
    private $excludeData = ['smtp-id', 'event', 'email', 'timestamp', 'ip', 'tls', 'cert_err', 'useragent', 'sg_message_id'];

    /**
     * Process events sent from SendGrid.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function events(Request $request)
    {
        $events = json_decode($request->getContent(), true);
        foreach ($events as $event) {
            $this->processEvent($event);
        }

        return response('');
    }

    /**
     * Process the event and insert it into the database.
     *
     * @param array $event
     */
    private function processEvent(array $event)
    {
        $entry = [
            'broker' => 'sendgrid',
            'message_id' => $event['sg_message_id'],
            'name' => $event['event'],
            'recipient' => $event['email'],
            'data' => array_diff_key($event, array_flip($this->excludeData)),
            'triggered_at' => Carbon::createFromTimestamp($event['timestamp']),
        ];

        Event::create($entry);
    }
}
