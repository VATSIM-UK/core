<?php

namespace App\Console\Commands\TeamSpeak;

use App\Libraries\TeamSpeak;
use Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Adapter\ServerQuery\Event;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TransportException;
use PlanetTeamSpeak\TeamSpeak3Framework\Helper\Signal;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Host;

class TeamSpeakDaemon extends TeamSpeakCommand
{
    protected static $connection;

    protected static $connectedClients = [];

    protected $signature = 'teaman:daemon';

    protected $description = 'TeamSpeak Management Daemon (TeaManD)';

    public function handle()
    {
        self::$connection = $this->establishConnection();
        $connectionFailures = 0;

        // main loop
        while (true) {
            try {
                self::$connection->getAdapter()->wait();
                $connectionFailures = 0;
            } catch (TransportException $e) {
                try {
                    self::$connection = $this->establishConnection();
                    $connectionFailures = 0;
                } catch (TransportException $e) {
                    // Connection failed, let the loop restart and try again
                    $connectionFailures++;
                    if ($connectionFailures == 3) {
                        throw new TeamSpeak3Exception('TeamSpeak Daemon failed to connect 3 times.');
                    }
                    $this->log('TeamSpeak connection failed: '.$e->getMessage().'. Trying again in 15 seconds...');
                    sleep(15);
                }
            }
        }
    }

    public static function clientJoinedEvent(Event $event, Host $host)
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
            TeamSpeak::checkMemberMandatoryNotifications($client, $member);
            TeamSpeak::checkClientNickname($client, $member);

            if (! TeamSpeak::clientIsProtected($client)) {
                TeamSpeak::checkClientServerGroups($client, $member);
                TeamSpeak::checkClientChannelGroups($client, $member);
            }
        } catch (ServerQueryException $e) {
            report($e);
            self::handleServerQueryException($e);
        } catch (Exception $e) {
            report($e);
            self::handleException($e);
        }
    }

    public static function clientLeftEvent(Event $event, Host $host)
    {
        if (isset(self::$connectedClients[$event->clid])) {
            unset(self::$connectedClients[$event->clid]);
        }
    }

    protected function establishConnection($attempt = 1)
    {
        try {
            // establish connection
            $connection = TeamSpeak::run('VATSIM UK Management Daemon', true);

            // register for events
            $connection->notifyRegister('server');

            Signal::getInstance()
                ->subscribe('notifyCliententerview', self::class.'::clientJoinedEvent');
            Signal::getInstance()
                ->subscribe('notifyClientleftview', self::class.'::clientLeftEvent');

            return $connection;
        } catch (ServerQueryException $e) {
            if ($e->getCode() === TeamSpeak::CLIENT_NICKNAME_INUSE) {
                $this->log("Nickname in use, attempt $attempt");
                sleep(15);

                return $this->establishConnection(++$attempt);
            } else {
                throw $e;
            }
        } catch (TransportException $e) {
            $exceptionCode = $e->getCode();
            if ($exceptionCode === TeamSpeak::CONNECTION_TIMED_OUT || $exceptionCode === TeamSpeak::CONNECTION_REFUSED) {
                $this->log("Connection timed out/refused, attempt $attempt");
                sleep(15);

                return $this->establishConnection(++$attempt);
            } else {
                throw $e;
            }
        }
    }
}
