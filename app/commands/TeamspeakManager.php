<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Controllers\Teamspeak\TeamspeakAdapter;
use Models\Mship\Account;
use Models\Mship\Qualification;
use Models\Teamspeak\Registration;
use Models\Teamspeak\Ban;

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
         *         No matching database registration (also remove from db)
         *         Nickname / alias doesn't match (with previous warnings)
         *         Idle
         *     Ban:
         *         Member is suspended/inactive
         *         Serving a current ban in the database
         *     Modify:
         *         Description, if not set / different
         * User:
         *     Check for new users and process them
         *     Update user credentials
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

                if ($client_account->is_banned) {
                    try {
                        $client->kick(TeamSpeak3::KICK_SERVER, "You are currently serving a VATSIM ban.");
                        $client->deleteDb();
                        $client_registration->delete();
                    } catch (Exception $e) {
                        if ($debug) echo "Error: " . $e->getMessage();
                    }
                } elseif ($client_account->is_teamspeak_banned) {
                    try {
                        $client->ban($client_account->is_teamspeak_banned, "You are currently serving a TeamSpeak ban.");
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
                }





            }

                // enforce

            // if no registration has been found
            if (!$new_client && !$client_registration) {
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
