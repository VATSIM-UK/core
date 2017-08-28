<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhook;

use App\Models\Email\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Response;

class Mailgun extends WebhookController
{
    /**
     * Parameters from Mailgun to exclude from the event data field.
     *
     * @var array
     */
    private $excludeData = ['Message-Id', 'message-id', 'event', 'recipient', 'timestamp', 'token', 'signature'];

    public function event(Request $request)
    {
        if (!$this->verifyMailgun($request)) {
            return Response::make('Unauthorised.', 406);
        }

        $entry = [
            'broker' => 'mailgun',
            'message_id' => $request->input('Message-Id') ?: $request->input('message-id'),
            'name' => $request->input('event'),
            'recipient' => $request->input('recipient'),
            'data' => array_diff_key($request->all(), array_flip($this->excludeData)),
            'triggered_at' => Carbon::createFromTimestamp($request->input('timestamp')),
        ];

        Event::create($entry);

        return response('');
    }

    private function verifyMailgun(Request $request)
    {
        $data = $request->input('timestamp').$request->input('token');
        $signature = hash_hmac('sha256', $data, env('MAILGUN_SECRET'));

        return $signature === $request->input('signature');
    }
}
