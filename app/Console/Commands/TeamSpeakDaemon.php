<?php

namespace App\Console\Commands;

use App\Exceptions\TeamSpeak\MaxConnectionAttemptsExceededException;
use App\Libraries\TeamSpeak;
use Cache;
use Carbon\Carbon;
use Exception;
use TeamSpeak3_Adapter_ServerQuery_Event;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use TeamSpeak3_Helper_Signal;
use TeamSpeak3_Node_Host;
use TeamSpeak3_Node_Server;
use TeamSpeak3_Transport_Exception;

class TeamSpeakDaemon extends TeamSpeakCommand
{
    /**
     * @var TeamSpeak3_Node_Server The TeamSpeak server connection.
     */
    protected static $connection;

    /**
     * @var array The connected clients, in the format $connectedClients[clid] = dbid;
     */
    protected static $connectedClients = [];

    /**
     * @var string The name and signature of the console command.
     */
    protected $signature = 'teaman:daemon';

    /**
     * @var string The console command description.
     */
    protected $description = 'TeamSpeak Management Daemon (TeaManD)';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        self::$connection = $this->establishConnection();
        self::$connection->notifyRegister('server');
        TeamSpeak3_Helper_Signal::getInstance()
            ->subscribe('notifyCliententerview', TeamSpeakDaemon::class . '::clientJoinedEvent');
        TeamSpeak3_Helper_Signal::getInstance()
            ->subscribe('notifyClientleftview', TeamSpeakDaemon::class . '::clientLeftEvent');

        // main loop
        while (true) {
            try {
                self::$connection->getAdapter()->wait();
            } catch (TeamSpeak3_Transport_Exception $e) {
                self::$connection = $this->establishConnection();
            }
        }
    }

    /**
     * Handle a client joining the server.
     *
     * @param TeamSpeak3_Adapter_ServerQuery_Event $event
     * @param TeamSpeak3_Node_Host                 $host
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function clientJoinedEvent(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
    {
        if ($event['client_type'] != 0) {
            return;
        }
        
        try {
            $client = $host->serverGetSelected()->clientGetById($event->clid);
            self::$command->currentMember = $client['client_database_id'];

            // log the client's clid and dbid in a data structure
            self::$connectedClients[$event->clid] = $client['client_database_id'];

            // perform the necessary checks on the client
            $member = TeamSpeak::checkClientRegistration($client);
            $client = TeamSpeak::checkClientDescription($client, $member);
            TeamSpeak::checkMemberStanding($client, $member);

            if (!TeamSpeak::clientIsProtected($client)) {
                TeamSpeak::checkMemberMandatoryNotifications($client, $member);
                TeamSpeak::checkClientNickname($client, $member);
                TeamSpeak::checkClientServerGroups($client, $member);
                TeamSpeak::checkClientChannelGroups($client, $member);
            }
        } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            self::handleServerQueryException($e);
        } catch (Exception $e) {
            self::handleException($e);
        }
    }

    /**
     * Handle a client leaving the server.
     *
     * @param TeamSpeak3_Adapter_ServerQuery_Event $event
     * @param TeamSpeak3_Node_Host                 $host
     */
    public static function clientLeftEvent(TeamSpeak3_Adapter_ServerQuery_Event $event, TeamSpeak3_Node_Host $host)
    {
        if (isset(self::$connectedClients[$event->clid])) {
            $dbid = self::$connectedClients[$event->clid];
            unset(self::$connectedClients[$event->clid]);

            // cache their dbid and the current datetime for n minutes
            Cache::put(TeamSpeak::CACHE_PREFIX_CLIENT_DISCONNECT . $dbid, Carbon::now(), 5);
        }
    }

    /**
     * Attempt to establish a connection to the TeamSpeak server.
     *
     * @param int $attempt
     * @return mixed|\TeamSpeak3_Adapter_Abstract
     * @throws \App\Exceptions\TeamSpeak\MaxConnectionAttemptsExceededException
     * @throws \TeamSpeak3_Adapter_ServerQuery_Exception
     * @throws \TeamSpeak3_Transport_Exception
     */
    protected function establishConnection($attempt = 1)
    {
        $max_attempts = 5;
        $sleep_factor = 5; // $max_attempts * sleep_factor = delay between attempts

        if ($attempt > $max_attempts) {
            throw new MaxConnectionAttemptsExceededException($max_attempts);
        }

        try {
            return TeamSpeak::run('VATSIM UK Management Daemon', true);
        } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            if ($e->getCode() === TeamSpeak::CLIENT_NICKNAME_INUSE) {
                $this->log('Nickname in use.');
                sleep($attempt * $sleep_factor);

                return $this->establishConnection($attempt + 1);
            } else {
                throw $e;
            }
        } catch (TeamSpeak3_Transport_Exception $e) {
            if ($e->getCode() === TeamSpeak::CONNECTION_TIMED_OUT) {
                $this->log('Connection timed out.');
                sleep($attempt * $sleep_factor);

                return $this->establishConnection($attempt + 1);
            } else {
                throw $e;
            }
        }
    }
}
