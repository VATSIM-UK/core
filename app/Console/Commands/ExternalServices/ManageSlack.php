<?php

namespace App\Console\Commands\ExternalServices;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use Bugsnag;
use Cache;
use Exception;
use SlackUser;

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

        foreach ($this->slackUsers->members as $slackUser) {
            try {
                if ($slackUser->name == 'admin' || $slackUser->name == 'slackbot') {
                    continue;
                }

                $localUser = Account::where('slack_id', $slackUser->id)->first();

                if (!$localUser) {
                    if ($this->userIsActive($slackUser)) {
                        $this->messageUserAdvisingOfRegistration($slackUser);
                    }

                    continue;
                }

                if ($localUser->is_banned && $this->userIsActive($slackUser)) {
                    $this->messageDsgAdvisingOfBannedUser($localUser, $slackUser);
                }

                if (!$localUser->isValidDisplayName($slackUser->profile->real_name) && $this->userIsActive($slackUser)) {
                    $this->messageAskingForRealName($localUser, $slackUser);
                }
            } catch (Exception $e) {
                Bugsnag::notifyException($e);

                $this->sendSlackError('ServerException processing client.', [
                    'id' => $slackUser->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function userIsActive($slackUser)
    {
        return Cache::remember("slack-user-{$slackUser->id}-presence", 5, function () use ($slackUser) {
            return SlackUser::getPresence($slackUser->id)->presence;
        }) == 'active';
    }

    private function messageAskingForRealName($localUser, $slackUser)
    {
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'Your current name does not match your VATSIM profile.', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "Please set your Slack name to '{$localUser->name}'.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "You can change your profile settings by clicking the 'Profile & Account' menu option.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
    }

    private function messageDsgAdvisingOfBannedUser($localUser, $slackUser)
    {
        $this->sendSlackError('A user who is banned, is using Slack.', [
            'User CID' => $localUser->id, 'Slack User' => $slackUser->id.' - '.$slackUser->name,
        ]);
    }

    private function messageUserAdvisingOfRegistration($slackUser)
    {
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'Your VATSIM UK and Slack accounts are not currently linked.', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'To link your accounts, please visit https://core.vatsim.uk and click the registration link for Slack.', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'If you have problems with this, please get in touch: https://helpdesk.vatsim.uk', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
    }
}
