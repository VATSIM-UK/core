<?php

namespace App\Http\Controllers\Slack;

use Redirect;
use Response;
use App\Models\Sys\Token;
use App\Models\Mship\Account;
use Vluzrmos\SlackApi\Facades\SlackUserAdmin;

class Registration extends \App\Http\Controllers\BaseController
{
    /**
     * Create a new Slack registration code.
     *
     * A user will only ever require one code, but visiting this page will not recycle their current registration code.
     *
     * @return mixed
     */
    public function getNew()
    {
        if ($this->account->slack_id != '') {
            return Redirect::route('mship.manage.dashboard')
                           ->withError("Your Slack account doesn't need registering.");
        }

        if (!$this->account->hasState('DIVISION')) {
            return Redirect::route('mship.manage.dashboard')
                           ->withError('You need to be a division member to register for Slack.');
        }

        if (!($_slackToken = $this->account->tokens()->ofType('slack_registration')->first())) {
            $_slackToken = Token::generate('slack_registration', false, $this->account);

            $slackUserAdmin = SlackUserAdmin::invite($this->account->email, [
                'first_name' => $this->account->name_first,
                'last_name'  => $this->account->name_last,
            ]);

            /*if($slackUserAdmin->ok != "true"){
                return Redirect::route("mship.manage.dashboard")
                               ->withError("There was an error with your slack registration: ".$slackUserAdmin->error);
            }*/
        }

        if ($_slackToken->expired) {
            return Redirect::route('mship.manage.dashboard')
                           ->withError("Your Slack registration seems to be complete, but your account isn't linked.  Please contact Web Services.");
        }

        $this->pageTitle = 'New Slack Registration';

        return $this->viewMake('slack.new')
                    ->with('slackToken', $_slackToken);
    }

    public function getConfirmed()
    {
        if (!$this->account->slack_id) {
            return Redirect::route('slack.new');
        }

        return $this->viewMake('slack.success');
    }

    // get status of registration
    public function postStatus(Token $slackToken)
    {
        if ($slackToken->type != 'slack_registration') {
            return Response::make('invalid');
        }

        if ($slackToken->related_id != $this->account->id) {
            return Response::make('auth.error');
        }

        if ($slackToken->expired) {
            return Response::make('expired');
        }

        if ($this->account->slack_id) {
            return Response::make('active');
        }

        return Response::make('pending');
    }
}
