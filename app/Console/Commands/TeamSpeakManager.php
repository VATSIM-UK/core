<?php

namespace App\Console\Commands;

use Exception;
use App\Libraries\TeamSpeak;
use TeamSpeak3_Adapter_ServerQuery_Exception;

class TeamSpeakManager extends TeamSpeakCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'teaman:runner';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TeamSpeak Management script.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $tscon = TeamSpeak::run('VATSIM UK Management Bot');

            // get all clients and initiate loop
            $clients = $tscon->clientList();
            foreach ($clients as $client) {
                try {
                    $this->currentMember = $client['client_database_id'];

                    // perform the necessary checks on the client
                    $member = TeamSpeak::checkClientRegistration($client);
                    $client = TeamSpeak::checkClientDescription($client, $member);
                    TeamSpeak::checkMemberStanding($client, $member);

                    if (!TeamSpeak::clientIsProtected($client)) {
                        TeamSpeak::checkMemberMandatoryNotifications($client, $member);
                        TeamSpeak::checkClientNickname($client, $member);
                        TeamSpeak::checkClientServerGroups($client, $member);
                        TeamSpeak::checkClientChannelGroups($client, $member);
                        TeamSpeak::checkClientIdleTime($client, $member);
                    }
                } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                    self::handleServerQueryException($e);
                } catch (Exception $e) {
                    self::handleException($e);
                }
            }
        } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            self::handleServerQueryException($e);
        } catch (Exception $e) {
            self::handleException($e);
        }
    }
}
