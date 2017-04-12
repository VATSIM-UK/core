<?php

namespace App\Console\Commands;

use SlackUser;
use App\Models\Mship\Account;

class SlackManager extends Command
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
            $localUser = Account::findWithSlackId($slackUser->id);
            $slackUser->presence = SlackUser::getPresence($slackUser->id)->presence;

            if ($slackUser->presence != 'active' || $slackUser->name == 'admin' || $slackUser->name == 'slackbot') {
                continue;
            }

            if (!$localUser || $localUser->exists == false) {
                $this->messageUserAdvisingOfRegistration($slackUser);
                continue;
            }

            if ($slackUser->presence == 'active' && $localUser->is_banned) {
                $this->messageDsgAdvisitingOfBannedUser($localUser, $slackUser);
            }

            if (!$localUser->isValidDisplayName($slackUser->real_name)) {
                $this->messageAskingForRealName($localUser, $slackUser);
            }

//            if(strcasecmp($localUser->email, $slackUser->profile->email) != 0){
//                $this->messageAskingForRealEmail($localUser, $slackUser);
//            }
        }
    }

    private function messageAskingForRealName($localUser, $slackUser)
    {
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "Your current name doesn't match your VATSIM profile.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "Please set your slack name to '".$localUser->name."'", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "You can change your profile settings by clicking the 'View Profile & Account' menu option.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
    }

    private function messageAskingForRealEmail($localUser, $slackUser)
    {
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "The email address '".$slackUser->profile->email."' is not your current VATSIM one.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'If your VATSIM one needs to change, please visit the membership services at https://vatsim.net', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "Alternatively, please set your Slack email to your current VATSIM one ('".$localUser->email."').", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
    }

    private function messageDsgAdvisitingOfBannedUser($localUser, $slackUser)
    {
        $this->sendSlackError('A user who is banned, is using Slack.', [
            'User CID' => $localUser->id, 'Slack User' => $slackUser->id.' - '.$slackUser->name,
        ]);
    }

    private function messageUserAdvisingOfRegistration($slackUser)
    {
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "You've not linked your VATSIM UK and Slack accounts.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, "It's incredibly important that you do this, otherwise I will continue to nag.", 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'To link your accounts, please visit https://core.vatsim-uk.co.uk and click the registration link for Slack.', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, 'If you have problems with this, please get in touch http://helpdesk.vatsim-uk.co.uk', 'VATSIM UK Slack Bot');
        $this->sendSlackMessagePlain($slackUser->id, '****************************************************', 'VATSIM UK Slack Bot');
    }
}
