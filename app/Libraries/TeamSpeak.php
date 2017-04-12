<?php

namespace App\Libraries;

use DB;
use Cache;
use TeamSpeak3;
use Carbon\Carbon;
use TeamSpeak3_Node_Client;
use App\Models\Mship\Account;
use App\Models\TeamSpeak\Channel;
use App\Models\TeamSpeak\ServerGroup;
use App\Models\TeamSpeak\ChannelGroup;
use App\Models\TeamSpeak\Registration;
use TeamSpeak3_Adapter_ServerQuery_Exception;
use App\Exceptions\TeamSpeak\RegistrationNotFoundException;
use App\Exceptions\TeamSpeak\ClientKickedFromServerException;

class TeamSpeak
{
    const CONNECTION_TIMED_OUT = 110;
    const CONNECTION_REFUSED = 111;
    const CLIENT_INVALID_ID = 512;
    const CLIENT_NICKNAME_INUSE = 513;
    const DATABASE_EMPTY_RESULT_SET = 1281;
    const PERMISSIONS_CLIENT_INSUFFICIENT = 2568;
    const CACHE_PREFIX_CLIENT_DISCONNECT = 'teamspeak_client_disconnect_';
    const CACHE_NOTIFICATION_MANDATORY = 'teamspeak_notify_mandatory_';
    const CACHE_NICKNAME_PARTIALLY_CORRECT = 'teamspeak_nickname_partially_correct_';
    const CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE = 'teamspeak_nickname_partially_correct_grace_';
    const CACHE_PREFIX_IDLE_NOTIFY = 'teamspeak_notify_idle_';

    /**
     * Connect to the TeamSpeak server.
     *
     * @param string $nickname
     * @param bool   $nonBlocking
     * @return \TeamSpeak3_Node_Server
     */
    public static function run($nickname = 'VATSIM UK TeamSpeak Bot', $nonBlocking = false)
    {
        $connectionUrl = sprintf(
            'serverquery://%s:%s@%s:%s/?nickname=%s&server_port=%s%s#no_query_clients',
            env('TS_USER'),
            env('TS_PASS'),
            env('TS_HOST'),
            env('TS_QUERY_PORT'),
            urlencode($nickname),
            env('TS_PORT'),
            $nonBlocking ? '&blocking=0' : ''
        );

        return TeamSpeak3::factory($connectionUrl);
    }

    /**
     * Check the client's registration.
     *
     * @param TeamSpeak3_Node_Client $client
     * @return Account
     * @throws RegistrationNotFoundException
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function checkClientRegistration(TeamSpeak3_Node_Client $client)
    {
        // try to find their existing registration
        $registration = self::getActiveRegistration($client);

        if (!is_null($registration)) {
            self::updateClientLoginInfo($client, $registration);

            return $registration->account;
        }

        // obtain the registration id and complete the registration
        $registration = self::getNewRegistration($client);
        if (is_null($registration)) {
            $dbid = $client['client_database_id'];
            self::pokeClient($client, trans('teamspeak.registration.notfound.poke'));
            self::kickClient($client, trans('teamspeak.registration.notfound.kick'));
            $client->deleteDb();

            throw new RegistrationNotFoundException(
                trans('teamspeak.registration.notfound.exception', ['dbid' => $dbid])
            );
        }

        self::completeNewRegistration($client, $registration);

        return $registration->account;
    }

    /**
     * Obtain the clients new registration, if they have one.
     *
     * @param TeamSpeak3_Node_Client $client
     * @return null|Registration
     * @throws TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function getNewRegistration(TeamSpeak3_Node_Client $client)
    {
        try {
            $customInfo = $client->customInfo();
        } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
            if ($e->getCode() !== self::DATABASE_EMPTY_RESULT_SET) {
                throw $e;
            } else {
                return;
            }
        }

        foreach ($customInfo as $info) {
            if ($info['ident'] == 'registration_id') {
                $registration = Registration::where('id', $info['value'])
                    ->whereNull('dbid')
                    ->first();

                return $registration;
            }
        }
    }

    /**
     * Obtain the client's active registration.
     *
     * @param TeamSpeak3_Node_Client $client
     * @return null|Registration
     */
    public static function getActiveRegistration(TeamSpeak3_Node_Client $client)
    {
        $registration = Registration::where('uid', $client['client_unique_identifier'])
            ->where('dbid', $client['client_database_id'])
            ->first();

        return $registration;
    }

    /**
     * Finalise a client's new registration.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Registration $registration
     */
    protected static function completeNewRegistration(TeamSpeak3_Node_Client $client, Registration $registration)
    {
        if ($registration->confirmation) {
            $registration->confirmation->delete();
        }

        $registration->uid = $client['client_unique_identifier'];
        $registration->dbid = $client['client_database_id'];
        $registration->save();

        self::updateClientLoginInfo($client, $registration);
    }

    /**
     * Update the clients login info.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Registration $registration
     */
    protected static function updateClientLoginInfo(TeamSpeak3_Node_Client $client, Registration $registration)
    {
        $registration->last_login = Carbon::now();
        $registration->last_ip = $client['connection_client_ip'];
        $registration->last_os = $client['client_platform'];
        $registration->save();
    }

    /**
     * Check a client's description is correct.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account                $member
     * @return TeamSpeak3_Node_Client
     */
    public static function checkClientDescription(TeamSpeak3_Node_Client $client, Account $member)
    {
        $description = sprintf(
            '%s %s (%s)',
            $member->name_first,
            $member->name_last,
            $member->id
        );

        if ($client->infoDb()['client_description'] != $description) {
            $client->modify(['client_description' => $description]);
            $client->getParent()->clientListReset();
            $client = $client->getParent()->clientGetById($client->getId());
        }

        return $client;
    }

    /**
     * Check a member is in good standing with the network and division.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account $member
     */
    public static function checkMemberStanding(TeamSpeak3_Node_Client $client, Account $member)
    {
        if ($member->is_network_banned) {
            $duration = 60 * 60 * 12; // 12 hours
            self::banClient($client, trans('teamspeak.ban.network.ban'), $duration);
        } elseif ($member->is_system_banned) {
            $duration = $member->system_ban->period_left;
            self::banClient($client, trans('teamspeak.ban.system.ban'), $duration);
        } elseif ($member->is_inactive) {
            self::deactivateClient($client, trans('teamspeak.inactive'));
        }
    }

    /**
     * Check a member has accepted any necessary notifications.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account                $member
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function checkMemberMandatoryNotifications(TeamSpeak3_Node_Client $client, Account $member)
    {
        if ($member->has_unread_must_acknowledge_notifications) {
            $recentlyDisconnected = Cache::has(self::CACHE_PREFIX_CLIENT_DISCONNECT.$client['client_database_id']);
            $recentlyNotified = Cache::has(self::CACHE_NOTIFICATION_MANDATORY.$client['client_database_id']);
            $timeSincePublished = $member->unread_must_acknowledge_time_elapsed;

            if ($timeSincePublished < 12) {
                if (!$recentlyNotified) {
                    self::pokeClient($client, trans('teamspeak.notification.mandatory.notify'));
                    Cache::put(
                        self::CACHE_NOTIFICATION_MANDATORY.$client['client_database_id'],
                        Carbon::now(),
                        20
                    );
                }
            } else {
                self::pokeClient($client, trans('teamspeak.notification.mandatory.poke'));
                self::kickClient($client, trans('teamspeak.notification.mandatory.kick'));
                throw new ClientKickedFromServerException;
            }
        } elseif ($member->has_unread_important_notifications) {
            self::pokeClient($client, trans('teamspeak.notification.important.poke.1'));
            self::pokeClient($client, trans('teamspeak.notification.important.poke.2'));
        }
    }

    /**
     * Check the client's nickname is correct.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account                $member
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function checkClientNickname(TeamSpeak3_Node_Client $client, Account $member)
    {
        if (!$member->isValidDisplayName($client['client_nickname'])) {
            $recentlyTold = Cache::has(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id']);
            $hasGracePeriod = Cache::has(self::CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE.$client['client_database_id']);
            // If their nickname doesn't even contain their name, or their grace period has ended
            if (!$member->isPartiallyValidDisplayName($client['client_nickname']) || ($recentlyTold && !$hasGracePeriod)) {
                self::pokeClient($client, trans('teamspeak.nickname.invalid.poke1'));
                self::pokeClient($client, trans('teamspeak.nickname.invalid.poke2'));
                self::kickClient($client, trans('teamspeak.nickname.invalid.kick'));
                Cache::forget(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id']);
                throw new ClientKickedFromServerException;
            }
            // If they are still in their grace period, lets not disturb.
            if (!$hasGracePeriod) {
                // We have a partially valid name. Could be incorrect callsgin? Lets give them a grace period
                self::pokeClient($client, trans('teamspeak.nickname.partiallyinvalid.poke1'));
                self::pokeClient($client, trans('teamspeak.nickname.partiallyinvalid.poke2'));
                self::messageClient($client, trans('teamspeak.nickname.partiallyinvalid.note', ['example' => $member->real_name.' - EGLL_N_TWR']));

                Cache::put(
                    self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id'],
                    Carbon::now(),
                    6
                );
                Cache::put(
                    self::CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE.$client['client_database_id'],
                    Carbon::now(),
                    3
                );
            }
        } else {
            Cache::forget(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id']);
        }
    }

    /**
     * Check the client is in the appropriate server groups.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account $member
     */
    public static function checkClientServerGroups(TeamSpeak3_Node_Client $client, Account $member)
    {
        $currentGroups = explode(',', $client['client_servergroups']);
        $serverGroups = ServerGroup::all();
        $memberQualifications = $member->active_qualifications;
        foreach ($serverGroups as $group) {
            $qualified = (!is_null($group->qualification) && $memberQualifications->contains('id', $group->qualification->id))
                || (!is_null($group->permission) && $member->hasPermission($group->permission));
            if (!in_array($group->dbid, $currentGroups) && $qualified) {
                $client->addServerGroup($group->dbid);
            } elseif (!in_array($group->dbid, $currentGroups) && starts_with($group->name, 'P0') && $member->qualifications_pilot->isEmpty()) {
                $client->addServerGroup($group->dbid);
            } elseif (in_array($group->dbid, $currentGroups) && starts_with($group->name, 'P0') && !$member->qualifications_pilot->isEmpty()) {
                $client->remServerGroup($group->dbid);
            } elseif (in_array($group->dbid, $currentGroups) && !starts_with($group->name, 'P0') && !$qualified && !$group->default) {
                $client->remServerGroup($group->dbid);
            }
        }
    }

    /**
     * Check the client is in the appropriate channel groups.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account                $member
     * @throws \TeamSpeak3_Adapter_ServerQuery_Exception
     */
    public static function checkClientChannelGroups(TeamSpeak3_Node_Client $client, Account $member)
    {
        $map = DB::table('teamspeak_channel_group_permission')->get();
        $defaultGroup = ChannelGroup::whereDefault(1)->first();

        foreach ($map as $permission) {
            try {
                $currentGroup = $client->getParent()->channelGroupClientList(null, $permission->channel_id, $client['client_database_id'])[0]['cgid'];
            } catch (TeamSpeak3_Adapter_ServerQuery_Exception $e) {
                if ($e->getCode() == self::DATABASE_EMPTY_RESULT_SET) {
                    $currentGroup = $defaultGroup->dbid;
                } else {
                    throw $e;
                }
            }

            if ($member->hasPermission($permission->permission_id) && $permission->channelgroup_id != $currentGroup) {
                $client->setChannelGroup($permission->channel_id, $permission->channelgroup_id);
            } elseif (!$member->hasPermission($permission->permission_id) && $currentGroup != $defaultGroup->dbid) {
                $client->setChannelGroup($permission->channel_id, $defaultGroup->dbid);
            }
        }
    }

    /**
     * Check the client's (allowed) idle time.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param Account                $member
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function checkClientIdleTime(TeamSpeak3_Node_Client $client, Account $member)
    {
        $idleTime = floor($client['client_idle_time'] / 1000 / 60); // minutes

        if ($member->hasPermission('teamspeak/idle/permanent')) {
            return;
        } elseif ($member->hasPermission('teamspeak/idle/temporary')) {
            $maxIdleTime = 120;
        } else {
            $maxIdleTime = 60;
        }

        $notified = Cache::has(self::CACHE_PREFIX_IDLE_NOTIFY.$client['client_database_id']);
        if ($idleTime >= $maxIdleTime) {
            self::pokeClient($client, trans('teamspeak.idle.kick.poke.1', ['maxIdleTime' => $maxIdleTime]));
            self::pokeClient($client, trans('teamspeak.idle.kick.poke.2'));
            self::kickClient($client, trans('teamspeak.idle.kick.reason'));
            throw new ClientKickedFromServerException;
        } elseif ($idleTime >= $maxIdleTime - 5 && !$notified) {
            self::pokeClient($client, trans('teamspeak.idle.poke', ['idleTime' => $idleTime]));
            Cache::put(self::CACHE_PREFIX_IDLE_NOTIFY.$client['client_database_id'], Carbon::now(), 5);
        } elseif ($idleTime >= $maxIdleTime - 15 && !$notified) {
            self::messageClient($client, trans('teamspeak.idle.message', ['idleTime' => $idleTime, 'maxIdleTime' => $maxIdleTime]));
            Cache::put(self::CACHE_PREFIX_IDLE_NOTIFY.$client['client_database_id'], Carbon::now(), 10);
        }
    }

    /**
     * Checks whether the client should be protected from being modified/disturbed.
     *
     * @param TeamSpeak3_Node_Client $client
     * @return bool
     */
    public static function clientIsProtected(TeamSpeak3_Node_Client $client)
    {
        $currentGroups = explode(',', $client['client_servergroups']);
        $currentChannel = $client['cid'];

        // if in a protected server group, client is protected
        $serverGroups = ServerGroup::whereProtected(1)->pluck('dbid')->toArray();
        foreach ($currentGroups as $group) {
            if (in_array($group, $serverGroups)) {
                return true;
            }
        }

        // if in a protected channel, client is protected
        $channel = Channel::find($currentChannel);
        if (!is_null($channel)) {
            return (bool) $channel->protected;
        } else {
            return false;
        }
    }

    /**
     * Messages the client.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param string $message
     */
    public static function messageClient(TeamSpeak3_Node_Client $client, $message)
    {
        $client->message($message);
    }

    /**
     * Pokes the client.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param string $message
     */
    public static function pokeClient(TeamSpeak3_Node_Client $client, $message)
    {
        $client->poke($message);
    }

    /**
     * Kicks the client.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param string $reason
     */
    public static function kickClient(TeamSpeak3_Node_Client $client, $reason)
    {
        $client->kick(TeamSpeak3::KICK_SERVER, $reason);
    }

    /**
     * Bans the client.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param string $reason
     * @param int $duration Duration in seconds.
     */
    public static function banClient(TeamSpeak3_Node_Client $client, $reason, $duration)
    {
        self::pokeClient($client, $reason);
        $client->ban($duration, $reason);
    }

    /**
     * Removes the client from the server and deletes them from the database.
     *
     * @param TeamSpeak3_Node_Client $client
     * @param string                 $reason
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function deactivateClient(TeamSpeak3_Node_Client $client, $reason)
    {
        self::pokeClient($client, $reason);
        self::kickClient($client, $reason);
        $client->deleteDb();
        self::getActiveRegistration($client)->delete($client->getParent());
        throw new ClientKickedFromServerException;
    }
}
