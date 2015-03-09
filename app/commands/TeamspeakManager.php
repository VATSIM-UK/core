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
         * Online:
         *     Report online statistics
         * Database maintenance:
         *     Remove old privilege keys from SQL and TS
         */

        define("TS_IDLE_MESSAGE", 1);
        define("TS_IDLE_POKE", 2);
        define("TS_IDLE_KICK", 3);

        $tscon = TeamSpeakAdapter::run();

        $protected_channels = array();
        $protected_channel_names = ['Staff Room', 'Exam Rooms']; // staff room, exam rooms

        foreach ($protected_channel_names as $channel_name) {
            $channel = $tscon->channelGetByName($channel_name);
            $protected_channels[] = $channel->getId();
            foreach ($channel->subChannelList() as $channel) {
                $protected_channels[] = $channel->getId();
            }
        }

        $qualifications = Qualification::all();
        $server_groups = $tscon->serverGroupList();
        $server_group_ids = array();
        $server_group_map = array();

        foreach ($server_groups as $group) {
            foreach ($qualifications as $qual) {
                if (preg_match('/'.$qual->code.'/', $group['name'])) {
                    $server_group_map[$qual->code] = $group;
                    $server_group_ids[$qual->code] = $group->getId();
                    continue;
                }
            }
            if (preg_match('/New/', $group['name'])) {
                $server_group_map['New'] = $group;
                $server_group_ids['New'] = $group->getId();
            }
        }

        // get all clients and initiate loop
        $clients = $tscon->clientList();
        foreach ($clients as $client) {
            try {
                try {
                    $client_custominfo = $client->customInfo();
                } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                    echo "Caught: " . $e->getMessage();
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
                    if ($new_client->confirmation) $new_client->confirmation->delete();
                    $new_client->uid = $client['client_unique_identifier'];
                    $new_client->dbid = $client['client_database_id'];
                    $new_client->status = 'active';
                    $new_client->save();
                }

                $client_registration = Registration::where('uid', '=', $client['client_unique_identifier'])->where('dbid', '=', $client['client_database_id'])->first();
                if ($client_registration) {
                    $client_account = $client_registration->account;

                    if ($client_account->is_banned || $client_account->is_inactive) {
                        try {
                            if ($client_account->is_banned) $message = "You are currently serving a VATSIM ban.";
                            else $message = "Your VATSIM membership is inactive - visit https://cert.vatsim.net/vatsimnet/statcheck.html";
                            $client->kick(TeamSpeak3::KICK_SERVER, $message);
                            $client->deleteDb();
                            $client_registration->delete();
                            continue;
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
                            continue;
                        } catch (Exception $e) {
                            if ($debug) echo "Error: " . $e->getMessage();
                        }
                    }

                    if (!in_array($client['client_channel_group_id'], $protected_channels)) {

                        $atc_rating = $client_account->qualification_atc->qualification->code;
                        $pilot_ratings = array();
                        foreach ($client_account->qualifications_pilot as $qualification) {
                            $pilot_ratings[] = $qualification->qualification->code;
                        }
                        $client_server_groups = explode(",", $client["client_servergroups"]);

                        // add missing groups
                        if (($index = array_search($server_group_ids[$atc_rating], $client_server_groups)) === FALSE) {
                            $server_group_map[$atc_rating]->clientAdd($client['client_database_id']);
                        } else {
                            unset($client_server_groups[$index]);
                        }
                        foreach ($pilot_ratings as $pilot_rating) {
                            if (($index = array_search($server_group_ids[$pilot_rating], $client_server_groups)) === FALSE) {
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
                        if (strcasecmp($client['client_nickname'], $nickname) !== 0 && !$client_account->isValidTeamspeakAlias($client['client_nickname'])) {

                            if (Carbon::now()->subMinutes(5)->gt($client_registration->last_nickname_warn)
                                && Carbon::now()->subMinutes(15)->lt($client_registration->last_nickname_warn)
                                && Carbon::now()->subMinutes(2)->gt($client_registration->last_nickname_kick)) {
                                $client->poke("Please use your full VATSIM-registered name - you may check this at http://core.vatsim-uk.co.uk/");
                                $client->poke("If you believe this is a mistake, please contact Web Services via http://helpdesk.vatsim-uk.co.uk/");
                                $client->kick(TeamSpeak3::KICK_SERVER, "Please use your full VATSIM-registered name.");
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'nick_kick']);
                                continue;
                            } elseif (Carbon::now()->subMinutes(15)->gt($client_registration->last_nickname_warn)) {
                                $client->message("Please use your full VATSIM-registered name - you may check this at http://core.vatsim-uk.co.uk/");
                                $client->message("You will be removed from the server if you do not change your name.");
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'nick_warn']);
                            }

                        }

                        $description = $client_account->name_first." ".$client_account->name_last." (".$client_account->account_id.")";
                        if ($client['client_description'] != $description)
                            $client->modify(['client_description' => $description]);

                        if ($new_client) continue; // if this is a new registration, don't process anything after this (due to segmentation fault)

                        // check idle time
                        $idle_time = $client['client_idle_time'] / 1000 / 60; // minutes
                        if (!$client_account->hasPermission("teamspeak/idle/extended") && !$client_account->hasPermission("teamspeak/idle/permanent")) {

                            if ($idle_time > 60) {
                                $client->kick(TeamSpeak3::KICK_SERVER, self::getMessageString(TS_IDLE_KICK, "60 minutes"));
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_kick']);
                                continue;
                            } elseif ($idle_time > 45 && Carbon::now()->subMinutes(16)->gt($client_registration->last_idle_poke)) {
                                $client->poke(self::getMessageString(TS_IDLE_POKE, "45 minutes"));
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_poke']);
                            } elseif ($idle_time > 30 && Carbon::now()->subMinutes(16)->gt($client_registration->last_idle_message)) {
                                $client->message(self::getMessageString(TS_IDLE_MESSAGE, "30 minutes", "60 minutes"));
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_message']);
                            }

                        } elseif ($client_account->hasPermission("teamspeak/idle/permanent")) {

                            if ($idle_time > 120 && Carbon::now()->subHour()->gt($client_registration->last_idle_message)) {
                                $client->message("You have been idle for more than two hours. Please try not abuse your idle exemption privileges.");
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_message']);
                            }

                        } elseif ($client_account->hasPermission("teamspeak/idle/extended")) {

                            if ($idle_time > 120) {
                                $client->kick(TeamSpeak3::KICK_SERVER, self::getMessageString(TS_IDLE_KICK, "2 hours"));
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_kick']);
                                continue;
                            } elseif ($idle_time > 105 && Carbon::now()->subMinutes(16)->gt($client_registration->last_idle_poke)) {
                                $client->poke(self::getMessageString(TS_IDLE_POKE, "1 hour 45 minutes"));
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_poke']);
                            } elseif ($idle_time > 90 && Carbon::now()->subMinutes(16)->gt($client_registration->last_idle_message)) {
                                $client->message(self::getMessageString(TS_IDLE_MESSAGE, "1 hour 30 minutes", "2 hours"));
                                Log::create(['registration_id' => $client_registration->id, 'type' => 'idle_message']);
                            }

                        }
                    }
                }

                // if no registration has been found
                if (!$new_client && !$client_registration) {
                    if (empty($client_custominfo)) continue; // old registration
                    $client->poke("We cannot find your TeamSpeak registration. To register, please visit http://core.vatsim-uk.co.uk/");
                    $client->poke("If the issue persists, please contact Web Services via http://helpdesk.vatsim-uk.co.uk/");
                    $client->kick(TeamSpeak3::KICK_SERVER, "No registration found.");
                    $client->deleteDb();
                    continue;
                }
            } catch (Exception $e) {
                $message = "TeaMan has encountered a previously unhandled error:\r\n\r\n";
                $message .= "Stack trace:\r\n\r\n";
                $message .= $e->getTraceAsString();
                $message .= "\r\nError message: " . $e->getMessage() . "\r\n";
                echo $message;
                mail("neil.farrington@vatsim-uk.co.uk", "TeaMan has failed you. Hire a new butler.", $message);
            }
        }

        // record online statistics
        file_put_contents("/home/NFarrington/httpdocs/teamspeak_viewer.html", $tscon->getViewer(new TeamSpeak3_Viewer_Html("http://static.tsviewer.com/images/ts3/viewer/", "images/countryflags/", "data:image")));

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

    protected static function getMessageString($param1, $param2 = "", $param3 = "") {
        if ($param1 == TS_IDLE_MESSAGE)
            $message =  "You have been idle in TeamSpeak for at least $param2. ";
            $message .= "We encourage members to disconnect if they plan to be away for extended periods. ";
            $message .= "You will be disconnected if you are idle for greater than $param3";
            return $message;
        if ($param1 == TS_IDLE_POKE)
            $message = "You have been idle for more than $param2. If you continue to be idle, you will be removed.";
            return $message;
        if ($param1 == TS_IDLE_KICK)
            $message =  "You have been removed from our TeamSpeak server for remaining idle for more than $param2. ";
            $message .= "You are welcome to re-connect to the server whenever you wish, ";
            $message .= "though please remember not to remain idle for extended periods.";
            return $message();
    }
}
