<?php

namespace App\Http\Controllers\Slack;

use App\Models\Sys\Token;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
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
        if ($this->account->slack_id) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('You already have a Slack registration with this account. Please contact the Web Services Department if you believe this to be an error.');
        }

        if (!($_slackToken = $this->account->tokens()->notExpired()->ofType('slack_registration')->first())) {
            DB::beginTransaction();
            $_slackToken = Token::generate('slack_registration', false, $this->account);

            $result = SlackUserAdmin::invite($this->account->email, [
                'first_name' => $this->account->name_first,
                'last_name' => $this->account->name_last,
            ]);

            if ($result->ok !== true) {
                DB::rollBack();
                Log::error("Error inviting {$this->account->real_name} ({$this->account->id }) to slack. Response: ". json_encode($result));

                return Redirect::route('mship.manage.dashboard')
                    ->withError('There was an error inviting you to join Slack. Please contact the Web Services Department.');
            }

            DB::commit();
        }

        if ($_slackToken->is_used) {
            return Redirect::route('mship.manage.dashboard')
                ->withError('Your Slack registration seems to be complete, but your account isn\'t linked.  Please contact Web Services.');
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
