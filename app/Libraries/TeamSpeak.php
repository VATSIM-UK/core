<?php

namespace App\Libraries;

use App\Exceptions\TeamSpeak\ClientKickedFromServerException;
use App\Exceptions\TeamSpeak\RegistrationNotFoundException;
use App\Models\Mship\Account;
use App\Models\TeamSpeak\Channel;
use App\Models\TeamSpeak\ChannelGroup;
use App\Models\TeamSpeak\Registration;
use App\Models\TeamSpeak\ServerGroup;
use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\TeamSpeak3Exception;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Client;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;
use PlanetTeamSpeak\TeamSpeak3Framework\TeamSpeak3;

/**
 * Provides static methods for managing TeamSpeak.
 */
class TeamSpeak
{
    const int CONNECTION_TIMED_OUT = 110;

    const int CONNECTION_REFUSED = 111;

    const int CLIENT_INVALID_ID = 512;

    const int CLIENT_NICKNAME_INUSE = 513;

    const int DATABASE_EMPTY_RESULT_SET = 1281;

    const int PERMISSIONS_CLIENT_INSUFFICIENT = 2568;

    const string CACHE_NOTIFICATION_MANDATORY = 'teamspeak_notify_mandatory_';

    const string CACHE_NICKNAME_PARTIALLY_CORRECT = 'teamspeak_nickname_partially_correct_';

    const string CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE = 'teamspeak_nickname_partially_correct_grace_';

    const string CACHE_PREFIX_IDLE_NOTIFY = 'teamspeak_notify_idle_';

    public static function enabled()
    {
        return config('services.teamspeak.host') && config('services.teamspeak.username') && config('services.teamspeak.password') && config('services.teamspeak.port') && config('services.teamspeak.query_port');
    }

    /**
     * Connect to the TeamSpeak server.
     *
     * @param  string  $nickname
     * @param  bool  $nonBlocking
     * @return Server
     */
    public static function run($nickname = 'VATSIM UK TeamSpeak Bot', $nonBlocking = false)
    {
        $connectionUrl = sprintf(
            'serverquery://%s:%s@%s:%s/?nickname=%s&server_port=%s%s#no_query_clients',
            urlencode(config('services.teamspeak.username')),
            urlencode(config('services.teamspeak.password')),
            config('services.teamspeak.host'),
            config('services.teamspeak.query_port'),
            urlencode($nickname),
            config('services.teamspeak.port'),
            $nonBlocking ? '&blocking=0' : ''
        );

        $factory = null;

        try {
            $factory = TeamSpeak3::factory($connectionUrl);
        } catch (ServerQueryException $e) {
            Log::error('TeamSpeak connection failed: '.$e->getMessage());

            if (stripos($e->getMessage(), 'nickname is already in use')) {
                // Try again in 3 seconds
                sleep(3);
                $factory = TeamSpeak3::factory($connectionUrl);
            }
        }

        return $factory;
    }

    /**
     * Check the client's registration.
     *
     * @return Account
     *
     * @throws RegistrationNotFoundException
     * @throws ServerQueryException
     */
    public static function checkClientRegistration(Client $client)
    {
        // try to find their existing registration
        $registration = self::getActiveRegistration($client);

        if (! is_null($registration)) {
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
     * @return null|Registration
     *
     * @throws TeamSpeak3Exception
     */
    public static function getNewRegistration(Client $client)
    {
        try {
            $customInfo = $client->customInfo();
        } catch (TeamSpeak3Exception $e) {
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
     * @return null|Registration
     */
    public static function getActiveRegistration(Client $client)
    {
        $registration = Registration::where('uid', $client['client_unique_identifier'])
            ->where('dbid', $client['client_database_id'])
            ->first();

        return $registration;
    }

    /**
     * Finalise a client's new registration.
     */
    protected static function completeNewRegistration(Client $client, Registration $registration)
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
     */
    protected static function updateClientLoginInfo(Client $client, Registration $registration)
    {
        $registration->last_login = Carbon::now();
        $registration->last_ip = $client['connection_client_ip'];
        $registration->last_os = $client['client_platform'];
        $registration->save();
    }

    /**
     * Check a client's description is correct.
     *
     * @return Client
     */
    public static function checkClientDescription(Client $client, Account $member)
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
     */
    public static function checkMemberStanding(Client $client, Account $member)
    {
        if ($member->is_network_banned) {
            $duration = 60 * 60 * 12; // 12 hours
            self::banClient($client, trans('teamspeak.ban.network.ban'), $duration);
        } elseif ($member->is_system_banned) {
            self::kickClient($client, trans('teamspeak.ban.system.ban'));
            sleep(2);
            $duration = $member->system_ban->period_left;
            self::banClient($client, trans('teamspeak.ban.system.ban'), $duration);
        } elseif ($member->is_inactive) {
            self::deactivateClient($client, trans('teamspeak.inactive'));
        }
    }

    /**
     * Check a member has accepted any necessary notifications.
     *
     *
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function checkMemberMandatoryNotifications(Client $client, Account $member)
    {
        if ($member->has_unread_must_acknowledge_notifications) {
            $recentlyNotified = Cache::has(self::CACHE_NOTIFICATION_MANDATORY.$client['client_database_id']);
            $timeSincePublished = $member->unread_must_acknowledge_time_elapsed;

            if ($timeSincePublished < 12) {
                if (! $recentlyNotified) {
                    self::pokeClient($client, trans('teamspeak.notification.mandatory.notify'));
                    Cache::put(
                        self::CACHE_NOTIFICATION_MANDATORY.$client['client_database_id'],
                        Carbon::now(),
                        20 * 60
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
     *
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function checkClientNickname(Client $client, Account $member)
    {
        if (! $member->isValidDisplayName($client['client_nickname']) && ! $member->isDuplicateDisplayName($client['client_nickname'])) {
            $recentlyTold = Cache::has(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id']);
            $hasGracePeriod = Cache::has(self::CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE.$client['client_database_id']);

            // Check to see if their name is at least partially right
            if ($member->isPartiallyValidDisplayName($client['client_nickname'])) {
                // If they have a grace period, allow it for now
                if (! $recentlyTold) {
                    // Give them a grace period if they haven't recently had one
                    $now = Carbon::now();
                    Cache::put(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id'], $now, 6 * 60);
                    Cache::put(self::CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE.$client['client_database_id'], $now, 3 * 60);

                    $recentlyTold = $hasGracePeriod = true;
                }

                if ($hasGracePeriod) {
                    self::pokeClient($client, trans('teamspeak.nickname.partiallyinvalid.poke1'));
                    self::pokeClient($client, trans('teamspeak.nickname.partiallyinvalid.poke2'));
                    self::messageClient($client, trans('teamspeak.nickname.partiallyinvalid.note', ['example' => $member->real_name.' - EGLL_N_TWR']));

                    return;
                }
            }

            // Either partially valid and grace period over, or doesn't even contain their name!
            self::pokeClient($client, trans('teamspeak.nickname.invalid.poke1'));
            self::pokeClient($client, trans('teamspeak.nickname.invalid.poke2'));
            self::kickClient($client, trans('teamspeak.nickname.invalid.kick'));
            Cache::forget(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id']);
            Cache::forget(self::CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE.$client['client_database_id']);
            throw new ClientKickedFromServerException;
        } else {
            Cache::forget(self::CACHE_NICKNAME_PARTIALLY_CORRECT.$client['client_database_id']);
            Cache::forget(self::CACHE_NICKNAME_PARTIALLY_CORRECT_GRACE.$client['client_database_id']);
        }
    }

    /**
     * Check the client is in the appropriate server groups.
     */
    public static function checkClientServerGroups(Client $client, Account $member)
    {
        $currentGroups = explode(',', $client['client_servergroups']);
        $serverGroups = ServerGroup::all();
        $memberQualifications = $member->active_qualifications;

        foreach ($serverGroups as $group) {
            $memberHasRequiredQualification = $group->qualification ? $memberQualifications->contains('id', $group->qualification->id) : false;
            $memberHasGroupPermission = $group->permission ? $member->hasPermissionTo($group->permission) : false;

            $qualifiesForGroup = $memberHasRequiredQualification || $memberHasGroupPermission;
            $alreadyInGroup = in_array($group->dbid, $currentGroups);

            if ($qualifiesForGroup && ! $alreadyInGroup) {
                $client->addServerGroup($group->dbid);
            } elseif (! $group->default && $alreadyInGroup && ! $qualifiesForGroup) {
                $client->remServerGroup($group->dbid);
            }
        }
    }

    /**
     * Check the client is in the appropriate channel groups.
     *
     *
     * @throws ServerQueryException
     */
    public static function checkClientChannelGroups(Client $client, Account $member)
    {
        $map = DB::table('teamspeak_channel_group_permission')->get();
        $defaultGroup = ChannelGroup::whereDefault(1)->first();

        foreach ($map as $permission) {
            try {
                $current = $client->getParent()->channelGroupClientList(null, $permission->channel_id, $client['client_database_id']);

                if ($current === []) {
                    $currentGroup = null;
                } else {
                    $currentGroup = $current[0]['cgid'];
                }
            } catch (ServerQueryException $e) {
                if ($e->getCode() == self::DATABASE_EMPTY_RESULT_SET) {
                    $currentGroup = $defaultGroup->dbid;
                } else {
                    throw $e;
                }
            }

            if ($member->hasPermissionTo($permission->permission_id) && $permission->channelgroup_id != $currentGroup) {
                $client->setChannelGroup($permission->channel_id, $permission->channelgroup_id);
            } elseif (! $member->hasPermissionTo($permission->permission_id) && $currentGroup != null && $currentGroup != $defaultGroup->dbid) {
                $client->setChannelGroup($permission->channel_id, $defaultGroup->dbid);
            }
        }
    }

    /**
     * Check the client's (allowed) idle time.
     *
     *
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function checkClientIdleTime(Client $client, Account $member)
    {
        $idleTime = floor($client['client_idle_time'] / 1000 / 60); // minutes

        if ($member->hasPermissionTo('teamspeak/idle/permanent') || $member->is_on_network) {
            return;
        } elseif ($member->hasPermissionTo('teamspeak/idle/temporary')) {
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
        } elseif ($idleTime >= $maxIdleTime - 5 && ! $notified) {
            self::pokeClient($client, trans('teamspeak.idle.poke', ['idleTime' => $idleTime]));
            Cache::put(self::CACHE_PREFIX_IDLE_NOTIFY.$client['client_database_id'], Carbon::now(), 5 * 60);
        } elseif (($maxIdleTime - 15 > 0) && ($idleTime >= $maxIdleTime - 15 && ! $notified)) {
            self::messageClient($client, trans('teamspeak.idle.message', ['idleTime' => $idleTime, 'maxIdleTime' => $maxIdleTime]));
            Cache::put(self::CACHE_PREFIX_IDLE_NOTIFY.$client['client_database_id'], Carbon::now(), 10 * 60);
        }
    }

    /**
     * Checks whether the client should be protected from being modified/disturbed.
     *
     * @return bool
     */
    public static function clientIsProtected(Client $client)
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
        if (! is_null($channel)) {
            return (bool) $channel->protected;
        } else {
            return false;
        }
    }

    /**
     * Messages the client.
     *
     * @param  string  $message
     */
    public static function messageClient(Client $client, $message)
    {
        $client->message($message);
    }

    /**
     * Pokes the client.
     *
     * @param  string  $message
     */
    public static function pokeClient(Client $client, $message)
    {
        $client->poke($message);
    }

    /**
     * Kicks the client.
     *
     * @param  string  $reason
     */
    public static function kickClient(Client $client, $reason)
    {
        $client->kick(TeamSpeak3::KICK_SERVER, $reason);
    }

    /**
     * Bans the client.
     *
     * @param  string  $reason
     * @param  int  $duration  Duration in seconds.
     */
    public static function banClient(Client $client, $reason, $duration)
    {
        self::pokeClient($client, $reason);
        $client->ban($duration, $reason);
    }

    /**
     * Removes the client from the server and deletes them from the database.
     *
     * @param  string  $reason
     *
     * @throws \App\Exceptions\TeamSpeak\ClientKickedFromServerException
     */
    public static function deactivateClient(Client $client, $reason)
    {
        self::pokeClient($client, $reason);
        self::kickClient($client, $reason);
        $client->deleteDb();
        self::getActiveRegistration($client)->delete($client->getParent());
        throw new ClientKickedFromServerException;
    }
}
