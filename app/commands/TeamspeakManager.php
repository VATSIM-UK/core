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

        // if specified, turn debug mode on
        if ($this->option("debug")) $debug = TRUE;
        else $debug = FALSE;

        define("TS_IDLE_MESSAGE", 1);
        define("TS_IDLE_POKE", 2);
        define("TS_IDLE_KICK", 3);

        $tscon = TeamSpeakAdapter::run("VATSIM UK Management Bot");

        // define protected channels (listed channels and their subchannels)
        $protected_clients = array();
        $protected_channel_names = ['Staff Room', 'Exam Rooms'];
        // for each protected channel
        foreach ($protected_channel_names as $channel_name) {
            $channel = $tscon->channelGetByName($channel_name);

            // get clients and add to array
            $client_list = $channel->clientList();
            foreach ($client_list as $client)
                $protected_clients[] = $client['client_database_id'];

            // get clients of subchannels and add to array
            foreach ($channel->subChannelList() as $channel) {
                $client_list = $channel->clientList();
                foreach ($client_list as $client)
                    $protected_clients[] = $client['client_database_id'];
            }
        }

        // map qualifications to their server groups
        $qualifications = Qualification::all();
        $server_groups = $tscon->serverGroupList();
        $server_group_ids = array();
        $server_group_map = array();
        foreach ($server_groups as $group) {
            foreach ($qualifications as $qual) {
                if (preg_match('/^'.$qual->code.'/', $group['name'])) {
                    $server_group_map[$qual->code] = $group;
                    $server_group_ids[$qual->code] = $group->getId();
                    continue;
                }
            }
            if (preg_match('/^New/', $group['name'])) {
                $server_group_map['New'] = $group;
                $server_group_ids['New'] = $group->getId();
            }
            if (preg_match('/^Member/', $group['name'])) {
                $server_group_map['Member'] = $group;
                $server_group_ids['Member'] = $group->getId();
            }
            if (preg_match('/^Server Admin/', $group['name'])) {
                $server_group_map['Admin'] = $group;
                $server_group_ids['Admin'] = $group->getId();
            }
            if (preg_match('/^P0/', $group['name'])) {
                $server_group_map['P0'] = $group;
                $server_group_ids['P0'] = $group->getId();
            }
        }

        // get all clients and initiate loop
        $counter = 0;
        $clients = $tscon->clientList();
        foreach ($clients as $client) {
            $counter++;
            //echo "Processing client $counter\n";
            // general try-catch -- catches any general TeamSpeak API issues
            try {
                // obtain the client's registration ID
                try {
                    $client_custominfo = $client->customInfo();
                } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                    //echo "Caught (likely empty custominfo): " . $e->getMessage() . "\n";
                    $client_custominfo = array();
                }

                // determine if the client is a new client (check regid and registration status)
                $new_client = FALSE;
                foreach ($client_custominfo as $custominfo) {
                    if ($custominfo['ident'] != "registration_id")
                        continue;

                    $new_client = Registration::
                        where('id', '=', $custominfo['value'])
                        ->where('status', '=', 'new')
                        ->where('registration_ip', '=', ip2long($client['connection_client_ip']))
                        ->first();
                    break;
                }

                // if the client is a new client, complete their registration details
                if ($new_client) {
                    $existing = Registration::where('uid', '=', $client['client_unique_identifier'])
                                    ->orWhere('dbid', '=', $client['client_database_id'])->first();
                    if ($existing) {
                        //$client->kick(TeamSpeak3::KICK_SERVER,
                        //            "You already have an active registration for this identity.");
                        $new_client->delete($tscon);
                        continue;
                    }
                    if ($new_client->confirmation) $new_client->confirmation->delete();
                    $new_client->uid = $client['client_unique_identifier'];
                    $new_client->dbid = $client['client_database_id'];
                    $new_client->status = 'active';
                    $new_client->save();
                }

                // determine if the client is an existing client
                $client_registration =
                    Registration::where('uid', '=', $client['client_unique_identifier'])
                                ->where('dbid', '=', $client['client_database_id'])->first();
                if ($client_registration) {
                    $client_account = $client_registration->account;

                    // save their current login details
                    $client_registration->last_login = Carbon::now();
                    $client_registration->last_ip = $client['connection_client_ip'];
                    $client_registration->last_os = $client['client_platform'];
                    $client_registration->save();

                    // check if the client is banned
                    if ($client_account->is_banned || $client_account->is_inactive) {
                        try {
                            if ($client_account->is_banned)
                                $message = "You are currently serving a VATSIM ban.";
                            else $message = "Your VATSIM membership is inactive - visit "
                                          . "https://cert.vatsim.net/vatsimnet/statcheck.html";
                            $client->kick(TeamSpeak3::KICK_SERVER, $message);
                            $client->deleteDb();
                            $client_registration->delete($tscon);
                            continue;
                        } catch (Exception $e) {
                            if ($debug) echo "Error: " . $e->getMessage();
                        }
                    } elseif ($client_account->is_teamspeak_banned) {
                        try {
                            $client->ban($client_account->is_teamspeak_banned,
                                "You are currently serving a TeamSpeak ban.");
                            if ($client_account->is_teamspeak_banned > 60 * 60 * 24 * 2) {
                                $client->deleteDb();
                                $client_registration->delete($tscon);
                            }
                            continue;
                        } catch (Exception $e) {
                            if ($debug) echo "Error: " . $e->getMessage();
                        }
                    }

                    // if the client isn't protected, check their groups and idle time

                    if (!in_array($client['client_database_id'], $protected_clients)) {

                        $atc_rating = $client_account->qualification_atc->qualification->code;
                        $pilot_ratings = array();
                        foreach ($client_account->qualifications_pilot as $qualification)
                            $pilot_ratings[] = $qualification->qualification->code;
                        if (empty($pilot_ratings)) $pilot_ratings[] = "P0";
                        $atc_training = array();
                        foreach ($client_account->qualifications_atc_training as $qualification)
                                $atc_training[] = $qualification->qualification->code;
                        $client_server_groups = explode(",", $client["client_servergroups"]);

                        // do they have server admin privileges?
                        if ($client_account->hasPermission("teamspeak/serveradmin")) {
                            if (($index = array_search($server_group_ids['Admin'],
                                                                $client_server_groups)) === FALSE)
                                $server_group_map['Admin']->clientAdd($client['client_database_id']);
                            else unset($client_server_groups[$index]);
                        }

                        // all registered users should be in the 'member' group
                        if (($index = array_search($server_group_ids['Member'],
                                                                $client_server_groups)) === FALSE)
                            $server_group_map['Member']->clientAdd($client['client_database_id']);
                        else unset($client_server_groups[$index]);

                        // do they have their appropriate ATC rating?
                        if (($index = array_search($server_group_ids[$atc_rating],
                                                                $client_server_groups)) === FALSE)
                            $server_group_map[$atc_rating]->clientAdd($client['client_database_id']);
                        else unset($client_server_groups[$index]);

                        // do they have their appropriate pilot ratings?
                        foreach ($pilot_ratings as $rating) {
                            if (($index = array_search($server_group_ids[$rating],
                                                                $client_server_groups)) === FALSE)
                                $server_group_map[$rating]->clientAdd($client['client_database_id']);
                            else unset($client_server_groups[$index]);
                        }

                        // do they have their appropriate atc training ratings?
                        foreach ($atc_training as $rating) {
                            if (($index = array_search($server_group_ids[$rating],
                                                                $client_server_groups)) === FALSE)
                                $server_group_map[$rating]->clientAdd($client['client_database_id']);
                            else unset($client_server_groups[$index]);
                        }

                        // remove any remaining groups that: weren't checked; have been mapped;
                        foreach ($client_server_groups as $group) {
                            if ($index = array_search($group, $server_group_ids))
                                $server_group_map[$index]->clientDel($client['client_database_id']);
                        }

                        // check registered name and ensure it's being used
                        $nickname = $client_account->name_first . " " . $client_account->name_last;
                        if (strcasecmp($client['client_nickname'], $nickname) !== 0
                            && !$client_account->isValidTeamspeakAlias($client['client_nickname'])) {

                            if (Carbon::now()->subMinutes(5)->
                                    gt($client_registration->last_nickname_warn)
                                && Carbon::now()->subMinutes(15)->
                                    lt($client_registration->last_nickname_warn)
                                && Carbon::now()->subMinutes(2)->
                                    gt($client_registration->last_nickname_kick)) {
                                $client->poke("Please use your full VATSIM-registered name - "
                                    . "you may check this at http://core.vatsim-uk.co.uk/");
                                $client->poke("If you believe this is a mistake, please contact "
                                    . "Web Services via http://helpdesk.vatsim-uk.co.uk/");
                                $client->kick(TeamSpeak3::KICK_SERVER, "Please use your full "
                                    . "VATSIM-registered name.");
                                Log::create(['registration_id' => $client_registration->id,
                                             'type' => 'nick_kick']);
                                continue;
                            } elseif (Carbon::now()->subMinutes(15)->
                                    gt($client_registration->last_nickname_warn)) {
                                $client->message("Please use your full VATSIM-registered name - "
                                    . "you may check this at http://core.vatsim-uk.co.uk/");
                                $client->message("You will be removed from the server if "
                                    . "you do not change your name.");
                                Log::create(['registration_id' => $client_registration->id,
                                             'type' => 'nick_warn']);
                            }

                        }

                        // check client description
                        $description = $client_account->name_first ." "
                                     . $client_account->name_last ." ("
                                     . $client_account->account_id .")";
                        $client_info = $client->infoDb();
                        if ($client_info['client_description'] != $description)
                            $client->modify(['client_description' => $description]);

                        // if this is a new registration, don't process anything after this
                        // (Reason: segmentation fault with idle time)
                        if ($new_client) continue;

                        // check idle time
                        $idle_time = $client['client_idle_time'] / 1000 / 60; // minutes
                        if (!$client_account->hasPermission("teamspeak/idle/extended")
                            && !$client_account->hasPermission("teamspeak/idle/permanent")) {

                            if ($idle_time > 60) {
                                $client->message(self::getMessageString(TS_IDLE_KICK, "60 minutes"));
                                $client->kick(TeamSpeak3::KICK_SERVER, "Idle timeout exceeded.");
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_kick']);
                                continue;
                            } elseif ($idle_time > 45 && Carbon::now()->subMinutes(16)->
                                    gt($client_registration->last_idle_poke)) {
                                $client->poke(self::getMessageString(TS_IDLE_POKE, "45 minutes"));
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_poke']);
                            } elseif ($idle_time > 30 && Carbon::now()->subMinutes(16)->
                                    gt($client_registration->last_idle_message)) {
                                $client->message(self::getMessageString(TS_IDLE_MESSAGE,
                                                                    "30 minutes", "60 minutes"));
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_message']);
                            }

                        } elseif ($client_account->hasPermission("teamspeak/idle/permanent")) {

                            if ($idle_time > 120 && Carbon::now()->subHour()->
                                    gt($client_registration->last_idle_message)) {
                                $client->message("You have been idle for more than two hours. "
                                    . "Please try not abuse your idle exemption privileges.");
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_message']);
                            }

                        } elseif ($client_account->hasPermission("teamspeak/idle/extended")) {

                            if ($idle_time > 120) {
                                $client->message(self::getMessageString(TS_IDLE_KICK, "2 hours"));
                                $client->kick(TeamSpeak3::KICK_SERVER, "Idle timeout exceeded.");
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_kick']);
                                continue;
                            } elseif ($idle_time > 105 && Carbon::now()->subMinutes(16)->
                                    gt($client_registration->last_idle_poke)) {
                                $client->poke(self::getMessageString(TS_IDLE_POKE,
                                                                            "1 hour 45 minutes"));
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_poke']);
                            } elseif ($idle_time > 90 && Carbon::now()->subMinutes(16)->
                                    gt($client_registration->last_idle_message)) {
                                $client->message(self::getMessageString(TS_IDLE_MESSAGE,
                                                                "1 hour 30 minutes", "2 hours"));
                                Log::create(['registration_id' => $client_registration->id,
                                    'type' => 'idle_message']);
                            }

                        }
                    }
                }

                // if no registration has been found
                if (!$new_client && !$client_registration) {
                    $client->poke("We cannot find your TeamSpeak registration. "
                        . "To register, please visit http://core.vatsim-uk.co.uk/");
                    $client->poke("Please note, your current IP address must be the same as the IP "
                        . "address you used to register.");
                    $client->poke("If the issue persists, please contact Web Services "
                        . "via http://helpdesk.vatsim-uk.co.uk/");
                    $client->kick(TeamSpeak3::KICK_SERVER, "No registration found.");
                    $client->deleteDb();
                    continue;
                }
            } catch (Exception $e) {
                $subject = "TeaMan has failed you. Hire a new butler.";
                $message = "TeaMan has encountered a previously unhandled error:\r\n\r\n"
                         . "Client: " . $description . "\r\n\r\n"
                         . "Stack trace:\r\n\r\n"
                         . $e->getTraceAsString()
                         . "\r\nError message: " . $e->getMessage() . "\r\n";
                echo $message;
                mail("neil.farrington@vatsim-uk.co.uk", $subject, $message);
            }
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

    /**
     * Return the TeamSpeak message string for a given action.
     *
     * @return string
     */
    protected static function getMessageString($param1, $param2 = "", $param3 = "") {
        if ($param1 == TS_IDLE_MESSAGE) {
            $message = "You have been idle in TeamSpeak for at least $param2. "
                     . "We encourage members to disconnect if they plan to be away for extended "
                     . "periods. You will be disconnected if you are idle for greater than $param3";
            return $message;
        }
        if ($param1 == TS_IDLE_POKE) {
            $message = "You have been idle for more than $param2. "
                     . "If you continue to be idle, you will be removed.";
            return $message;
        }
        if ($param1 == TS_IDLE_KICK) {
            $message =  "You have been removed from our TeamSpeak server for remaining idle for "
                     . "more than $param2. You are welcome to re-connect to the server whenever "
                     . "you wish, though please remember not to remain idle for extended periods.";
            return $message;
        }
    }
}
