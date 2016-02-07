<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Requests;
use App\Libraries\Dropbox as DropboxLibrary;
use App\Libraries\Slack as SlackLibrary;
use App\Models\Sys\Token;
use Cache;
use Illuminate\Http\Request;

class Slack extends WebhookController
{
    private $slackPayload = [];
    private $commandRoutes = [
        "/register" => [
            "token" => "",
            "method" => "getRegister",
        ],
    ];

    /**
     * Handles the slack payload and routes to appropriate function(s).
     *
     * @param \Illuminate\Http\Request $request The HTTP Request object.
     *
     * @return \Illuminate\Http\Response
     */
    public function anyRouter(Request $request){
        $this->slackPayload = $request->all();

        if(!($route = $this->route($this->payload("command")))){
           return Response::make("Invalid command routing.  Please seek support (web-support@vatsim-uk.co.uk).");
        }

        if($route['token'] != $this->payload("token")){
            return Response::make("Malformed command.  If error persists, seek support (web-support@vatsim-uk.co.uk).");
        }

        return Response::make($this->{$route["method"]});
    }

    private function getRegister(){
        $slackToken = Token::isType("slack_registration")->hasCode($this->payload("text"))->first();

        if(!$slackToken || !$slackToken->exists){
            return "Invalid registration token provided.";
        }

        $account = $slackToken->related;

        if(!$account || !$account->exists){
            return "Invalid user associated with token.";
        }

        $account->slack_id = $this->payload("user_id");
        $account->save();

        $slackToken->consume();

        return "Registration completed successfully, ".$account->name." (".$account->account_id.").";
    }

    private function payload($key){
        return array_get($this->slackPayload, $key, null);
    }

    private function route($command){
        if(array_key_exists($command, $this->commandRoutes)){
            return $this->commandRoutes[$command];
        }

        return null;
    }
}
