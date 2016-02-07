<?php namespace App\Http\Controllers\Slack;

use App\Models\Sys\Token;
use Redirect;
use Response;
use App\Models\Mship\Account;
use App\Models\Teamspeak\Registration as RegistrationModel;
use App\Models\Teamspeak\Confirmation as ConfirmationModel;
use App\Http\Controllers\Teamspeak\TeamspeakAdapter;

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
        if($this->_account->slack_id != ""){
            return Redirect::route("mship.manage.dashboard")->withError("Your slack account doesn't need registrating.");
        }

        if(!($_slackToken = $this->_account->tokens()->ofType("slack_registration")->first())){
            $_slackToken = Token::generate("slack_registration", false, $this->_account);
        }

        if($_slackToken->expired){
            return Redirect::route("mship.manage.dashboard")->withError("Your Slack registration seems to be complete, but your account isn't linked.  Please contact web services.");
        }

        $this->_pageTitle = "New Slack Registration";
        return $this->viewMake("slack.new")
                    ->with("slackToken", $_slackToken);
    }

    public function getConfirmed()
    {
        if(!$this->_account->slack_id){
            return Redirect::route("slack.new");
        }

        return $this->viewMake("slack.success");
    }

    // get status of registration
    public function postStatus(Token $slackToken)
    {
        if($slackToken->type != "slack_registration"){
            return Response::make("invalid");
        }

        if($slackToken->related_id != $this->_account->account_id){
            return Response::make("auth.error");
        }

        if($slackToken->expired){
            return Response::make("expired");
        }

        if($this->_account->slack_id){
            return Response::make("active");
        }

        return Response::make("pending");
    }
}
