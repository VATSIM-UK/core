<?php

namespace App\Console\Commands;

use App\Models\Mship\Account;
use Illuminate\Console\Command;
use \SlackChat;
use \SlackUser;
use \SlackUserAdmin;

class SlackManager extends aCommand
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
        $this->checkUsersAreRegisteredInLocalDB();
    }

    private function checkUsersAreRegisteredInLocalDB()
    {
        $slackUsers = SlackUser::lists();

        foreach($slackUsers->members as $slackUser){
            $localUser = Account::findWithSlackId($slackUser->id);

            if(!$localUser || $localUser->exists == false){
                $this->attemptToAssociateSlackUserWithLocalUser($slackUser);
            }

            if($localUser->is_banned){
                $this->sendSlackError("A user who is banned, is using Slack.", [
                    "User CID" => $localUser->account_id, "Slack User" => $slackUser->id." - ".$slackUser->name
                ]);
            }
        }
    }

    private function attemptToAssociateSlackUserWithLocalUser($slackUser){
        $localUser = Account::find($slackUser->name);

        if($localUser->slack_id != ""){
            return $localUser;
        }

        if($localUser && $localUser->exists == true){
            $localUser->slack_id = $slackUser->id;
            $localUser->save();

            $this->sendSlackMessagePlain($localUser->slack_id, "I've now linked your slack account with VATSIM Account ".$localUser->account_id.".", "VATSIM UK Slack Bot");
            $this->sendSlackMessagePlain($localUser->slack_id, "If this was wrong, please let us know http://helpdesk.vatsim-uk.co.uk", "VATSIM UK Slack Bot");
            $this->sendSlackMessagePlain($localUser->slack_id, "You can now set your Slack username to anything you want by visiting https://vatsim-uk.slack.com/account/settings#username", "VATSIM UK Slack Bot");
            $this->sendSlackMessagePlain($localUser->slack_id, "I would recommend you set it to ".$localUser->name_first." ".$localUser->name_last.".", "VATSIM UK Slack Bot");

            return $localUser;
        }

        $this->sendSlackMessagePlain($slackUser->id, "I've attempted to link your Slack account and your VATSIM UK account, but I struggled.", "VATSIM UK Slack Bot");
        $this->sendSlackMessagePlain($slackUser->id, "Please make sure that your username matches your VATSIM CID.", "VATSIM UK Slack Bot");
        $this->sendSlackMessagePlain($slackUser->id, "You can do this by visiting https://vatsim-uk.slack.com/account/settings#username", "VATSIM UK Slack Bot");
        $this->sendSlackMessagePlain($slackUser->id, "Once I link your accounts, you can set your Slack Username to *anything* you want, as long as it's friendly.", "VATSIM UK Slack Bot");
        $this->sendSlackMessagePlain($slackUser->id, "If I keep sending you this message, please get in touch http://helpdesk.vatsim-uk.co.uk", "VATSIM UK Slack Bot");
    }
}
