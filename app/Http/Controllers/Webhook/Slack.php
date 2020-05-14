<?php

namespace App\Http\Controllers\Webhook;

use App\Models\Sys\Token;
use Illuminate\Http\Request;
use Response;

class Slack extends WebhookController
{
    private $slackPayload = [];
    private $commandRoutes = [
        '/register' => [
            'token' => '',
            'method' => 'getRegister',
        ],
    ];

    public function __construct()
    {
        $this->commandRoutes['/register']['token'] = config('services.slack.token_register');
    }

    /**
     * Handles the slack payload and routes to appropriate function(s).
     *
     * @param \Illuminate\Http\Request $request The HTTP Request object.
     *
     * @return \Illuminate\Http\Response
     */
    public function anyRouter(Request $request)
    {
        $this->slackPayload = $request->all();

        if (! ($route = $this->route($this->payload('command')))) {
            return Response::make('Invalid command routing.  Please seek support (web-support@vatsim.uk).');
        }

        if ($route['token'] != $this->payload('token')) {
            return Response::make('Malformed command.  If error persists, seek support (web-support@vatsim.uk).');
        }

        return Response::make(call_user_func([$this, $route['method']]));
    }

    private function getRegister()
    {
        $slackToken = Token::ofType('slack_registration')->hasCode($this->payload('text'))->first();

        if (! $slackToken || ! $slackToken->exists) {
            return 'Invalid registration token provided.';
        }

        $account = $slackToken->related;

        if (! $account || ! $account->exists) {
            return 'Invalid user associated with token.';
        }

        if ($account->slack_id || $account->slack_id != '') {
            return "You've already registered - not further registrations are permitted.";
        }

        $account->slack_id = $this->payload('user_id');
        $account->save();

        $slackToken->consume();

        return 'Registration completed successfully, '.$account->name.' ('.$account->id.').';
    }

    private function payload($key)
    {
        return array_get($this->slackPayload, $key, null);
    }

    private function route($command)
    {
        if (array_key_exists($command, $this->commandRoutes)) {
            return $this->commandRoutes[$command];
        }
    }
}
