<?php

namespace App\Console\Commands\ExternalServices;

use Cache;
use Bugsnag;
use Exception;
use SlackUser;
use App\Models\Mship\Account;
use App\Console\Commands\Command;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class ManageSlack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slack:manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform the general slack management & tidying';

    /**
     * All slack users currently in the team.
     *
     * @var array
     */
    protected $slackUsers = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->slackUsers = SlackUser::lists();

        if ($this->slackUsers->ok == false && $this->slackUsers->error = 'invalid_auth') {
            // Incorrect server credentials
            \Log::error('Slack credentials invalid!');
            $this->error('Slack credentials invalid!');

            return;
        }

        foreach ($this->slackUsers->members as $slackUser) {
            start:
            try {
                if ($slackUser->deleted || $slackUser->is_bot || $slackUser->is_admin || $slackUser->name === 'slackbot') {
                    continue;
                }

                $localUser = Account::where('slack_id', $slackUser->id)->first();

                if (! $localUser) {
                    if ($this->userIsActive($slackUser)) {
                        // Try to find matching account - 1st their primary email
                        $matchAccount = Account::where('email', $slackUser->profile->email)->orWhereHas('secondaryEmails', function ($query) use ($slackUser) {
                            $query->where('email', $slackUser->profile->email);
                        })->where('slack_id', null)->first();
                        if ($matchAccount) {
                            $matchAccount->slack_id = $slackUser->id;
                            $matchAccount->save();
                            $this->messageUserAdvisingOfAutomaticRegistration($slackUser);
                            continue;
                        }
                        $this->messageUserAdvisingOfRegistration($slackUser);
                    }

                    continue;
                }

                if ($localUser->is_banned && $this->userIsActive($slackUser)) {
                    $this->messageDsgAdvisingOfBannedUser($localUser, $slackUser);
                }

                if (! $localUser->isValidDisplayName($slackUser->profile->real_name) && $this->userIsActive($slackUser)) {
                    $this->messageAskingForRealName($localUser, $slackUser);
                }
            } catch (ClientException $e) {
                if ($e->getCode() === 429) {
                    $retryAfter = (int) $e->getResponse()->getHeader('Retry-After')[0];
                    sleep(++$retryAfter);
                    goto start;
                }

                Bugsnag::notifyException($e);
            } catch (Exception $e) {
                Bugsnag::notifyException($e);
            }
        }
    }

    private function userIsActive($slackUser)
    {
        return Cache::remember("slack-user-{$slackUser->id}-presence", 5, function () use ($slackUser) {
            try {
                $user = SlackUser::getPresence($slackUser->id);
                if (! $user || ! $user->ok) {
                    // Most likely a slack error.
                    return 'active';
                }

                return $user->presence;
            } catch (ServerException $e) {
                // Server exception - not our fault. We will assume they are active.
                return 'active';
            }
        }) == 'active';
    }

    private function messageAskingForRealName($localUser, $slackUser)
    {
        $messages = [
            '****************************************************',
            'Your current name does not match your VATSIM profile.',
            "Please set your Slack name to '{$localUser->name}'.",
            "You can change your profile settings by clicking the 'Profile & Account' menu option.",
            '****************************************************',
        ];

        $this->sendMessagesToUser($messages, $slackUser);
    }

    private function messageDsgAdvisingOfBannedUser($localUser, $slackUser)
    {
        $this->sendSlackError('A user who is banned, is using Slack.', [
            'User CID' => $localUser->id, 'Slack User' => $slackUser->id.' - '.$slackUser->name,
        ]);
    }

    private function messageUserAdvisingOfRegistration($slackUser)
    {
        $messages = [
            '****************************************************',
            'Your VATSIM UK and Slack accounts are not currently linked.',
            'To link your accounts, please visit https://core.vatsim.uk and click the registration link for Slack.',
            'If you have problems with this, please get in touch: https://helpdesk.vatsim.uk',
            '****************************************************',
        ];

        $this->sendMessagesToUser($messages, $slackUser);
    }

    private function messageUserAdvisingOfAutomaticRegistration($slackUser)
    {
        $messages = [
            '****************************************************',
            'Hi '.$slackUser->real_name.',',
            'We have found an account matching your email on VATSIM UK Core.',
            'As such, we have automatically linked this slack account to core for you',
            'Please let us know via the helpdesk if this has occurred erroneously',
            '****************************************************',
        ];

        $this->sendMessagesToUser($messages, $slackUser);
    }

    private function sendMessagesToUser($messages, $slackUser)
    {
        foreach ($messages as $message) {
            $this->sendSlackMessagePlain($slackUser->id, $message, 'VATSIM UK Slack Bot');
        }
    }
}
