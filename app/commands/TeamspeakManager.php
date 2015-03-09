<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Carbon\Carbon;

use Controllers\Teamspeak\TeamspeakAdapter;
use Models\Mship\Account;
use Models\Mship\Qualification;
use Models\Teamspeak\Registration;
use Models\Teamspeak\Ban;
use Models\Teamspeak\Log;

class TeamspeakManager extends aCommand {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'TeaMan:WakeUp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TeamSpeak Management script.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire() {
        /**
         * Enforcer:
         *     Protect clients
         *         exclude certain channels - Exam Rooms, Staff Room
         *         extend timeout for certain channels - Training Rooms
         *         Division staff - extend timeout and warnings
         *     Kick:
         * /        No matching database registration (also remove from db)
         *         Nickname / alias doesn't match (with previous warnings)
         *         Idle
         * /    Ban:
         * /        Member is suspended/inactive
         * /        Serving a current ban in the database
         * /    Modify:
         * /        Description, if not set / different
         * /User:
         * /    Check for new users and process them
         * /    Update user credentials
         * Online:
         *     Report online statistics
         * Database maintenance:
         *     Remove old privilege keys from SQL and TS
         *
         * Remember:
         *      Check if regid on the client matches the account, verify name/ip etc.
         *      When a registration is removed, check for existing dbid on the server
         *      Add logging
         */
try {
        $channels_protected = [];
        $channels_semi_protected = [];

        $tscon = TeamSpeakAdapter::run();

        $qualifications = Qualification::all();
        $server_groups = $tscon->serverGroupList();
        $server_group_ids = array();
        $server_group_map = array();

        foreach ($qualifications as $qual) {
            foreach ($server_groups as $group) {
                if (preg_match('/'.$qual->code.'/', $group['name'])) {
                    $server_group_map[$qual->code] = $group;
                    $server_group_ids[$qual->code] = $group->getId();
                }
            }
        }

        var_dump($server_group_ids);

        // get all clients and initiate loop
        $clients = $tscon->clientList();
        foreach ($clients as $client) {

            try {
                $client_custominfo = $client->customInfo();
            } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                echo "Caught . " . $e->getMessage();
                $client_custominfo = array();
            }

            $new_client = FALSE;
            foreach ($client_custominfo as $custominfo) {
                if ($custominfo['ident'] != "registration_id")
                    continue;

                $new_client = Registration::where('id', '=', $custominfo['value'])
                                          ->where('status', '=', 'new')->first();
                break;
            }

            if ($new_client) {
                $new_client->confirmation->delete();
                $new_client->uid = $client['client_unique_identifier'];
                $new_client->database_id = $client['client_database_id'];
                $new_client->status = 'active';
                $new_client->save();
            }

            $client_registration = Registration::where('uid', '=', $client['client_unique_identifier'])->first();
            if ($client_registration) {
                $client_account = $client_registration->account;

                if ($client_account->is_banned || $client_account->is_inactive) {
                    try {
                        if ($client_account->is_banned) $message = "You are currently serving a VATSIM ban.";
                        else $message = "Your VATSIM membership is inactive - visit https://cert.vatsim.net/vatsimnet/statcheck.html";
                        $client->kick(TeamSpeak3::KICK_SERVER, $message);
                        $client->deleteDb();
                        $client_registration->delete();
                    } catch (Exception $e) {
                        if ($debug) echo "Error: " . $e->getMessage();
                    }
                } elseif ($client_account->is_teamspeak_banned) {
                    try {
                        $client->ban($client_account->is_teamspeak_banned, "You are currently serving a TeamSpeak ban.");
                        if ($client_account->is_teamspeak_banned > 60 * 60 * 24 * 2) {
                            $client->deleteDb();
                            $client_registration->delete();
                        }
                    } catch (Exception $e) {
                        if ($debug) echo "Error: " . $e->getMessage();
                    }
                }

                if (!in_array($client['client_channel_group_id'], $channels_protected)) {

                    $atc_rating = $client_account->qualification_atc->qualification->code;
                    $pilot_ratings = array();
                    foreach ($client_account->qualifications_pilot as $qualification) {
                        $pilot_ratings[] = $qualification->qualification->code;
                    }
                    $client_server_groups = explode(",", $client["client_servergroups"]);

                    // add missing groups
                    if (!($index = array_search($server_group_ids[$atc_rating], $client_server_groups))) {
                        $server_group_map[$atc_rating]->clientAdd($client['client_database_id']);
                    } else {
                        unset($client_server_groups[$index]);
                    }
                    foreach ($pilot_ratings as $pilot_rating) {
                        if (!($index = array_search($server_group_ids[$pilot_rating], $client_server_groups))) {
                            $server_group_map[$pilot_rating]->clientAdd($client['client_database_id']);
                        } else {
                            unset($client_server_groups[$index]);
                        }
                    }

                    // check any remaining groups they have
                    foreach ($client_server_groups as $group) {
                        if ($index = array_search($group, $server_group_ids)) {
                            $server_group_map[$index]->clientDel($client['client_database_id']);
                        }
                    }

                    $nickname = $client_account->name_first . " " . $client_account->name_last;
                    if ($client['client_nickname'] != $nickname && !$client_account->isValidTeamspeakAlias($client['client_nickname'])) {
                        // check number of times warned, and decide on action
                    }

                    $description = $client_account->name_first." ".$client_account->name_last." (".$client_account->account_id.")";
                    if ($client['client_description'] != $description)
                        $client->modify(['client_description' => $description]);

                    // check idle time
                    $idle_time = $client['client_idle_time'] / 1000 / 60; // minutes
                    $now = Carbon::now();
                    if ($idle_time > 60) {
                        $message =  "You have been removed from our TeamSpeak server for remaining idle for more than an hour. ";
                        $message .= "You are welcome to re-connect to the server whenever you wish, ";
                        $message .= "though please remember not to remain idle for extended periods.";
                        $client->kick(TeamSpeak3::KICK_SERVER, $message);
                        Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_kick']);
                    } elseif ($idle_time > 45 && $now->subMinutes(16)->gt($client_registration->last_idle_poke)) {
                        $message = "You have been idle for at least 45 minutes. If you continue to be idle, you will be removed.";
                        $client->poke($message);
                        Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_poke']);
                    } elseif ($idle_time > 30 && $now->subMinutes(16)->gt($client_registration->last_idle_message)) {
                        $message =  "You have been idle in TeamSpeak for at least 30 minutes. ";
                        $message .= "We encourage members to disconnect if they plan to be away for extended periods. ";
                        $message .= "Members idle for greater than an hour will be automatically removed.";
                        $client->message($message);
                        Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_message']);
                    }

                }





            }

            // if no registration has been found
            if (!$new_client && !$client_registration) {
                if (empty($client_custominfo)) continue; // old registration


                // check if member by this name or ip exists, and log it
                // log their database id
                // poke -- registration not found, please re-register or contact support
                // kick (No registration found.)
                // delete from TS database
                $client->kick(TeamSpeak3::KICK_SERVER, "No registration found.");
                $client->deleteDb();
                continue;
            }
        }

        // record online statistics

        // check and process database

} catch (Exception $e) {
    echo $e->getTraceAsString();
    echo "\nOccurred on line: " . $e->getLine() . "\n";
    echo $e->getMessage() . "\n";
}
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions() {
        return array(
            array("debug", "d", InputOption::VALUE_NONE, "Enable debug output."),
        );
    }
}
