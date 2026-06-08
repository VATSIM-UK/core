<?php

namespace App\Services\TeamSpeak;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\TeamSpeak\AtcGroupAssignment;
use App\Models\TeamSpeak\AtcServerGroup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PlanetTeamSpeak\TeamSpeak3Framework\Exception\ServerQueryException;
use PlanetTeamSpeak\TeamSpeak3Framework\Node\Server;

class AtcServerGroupService
{
    public function assign(Account $account, string $callsign, Server $server): void
    {
        DB::transaction(function () use ($account, $callsign, $server) {
            $this->releaseExisting($account, $server);

            $group = $this->findOrCreateGroup($callsign, $server);

            $registration = $account->teamspeakRegistrations()
                ->whereNotNull('dbid')
                ->latest('last_login')
                ->first();

            if (! $registration) {
                Log::warning("No TS registration for account {$account->id}, skipping ATC group assign.");

                return;
            }

            $server->request("servergroupaddclient sgid={$group->ts_sgid} cldbid={$registration->dbid}");

            AtcGroupAssignment::updateOrCreate(
                ['account_id' => $account->id],
                ['atc_server_group_id' => $group->id]
            );

            Log::info("Assigned account {$account->id} to ATC group '{$callsign}' (sgid={$group->ts_sgid})");
        });
    }

    public function release(Account $account, Server $server): void
    {
        DB::transaction(function () use ($account, $server) {
            $this->releaseExisting($account, $server);
        });
    }

    private function releaseExisting(Account $account, Server $server): void
    {
        $assignment = AtcGroupAssignment::with('serverGroup')
            ->where('account_id', $account->id)
            ->first();

        if (! $assignment) {
            return;
        }

        $group = $assignment->serverGroup;

        $registration = $account->teamspeakRegistrations()
            ->whereNotNull('dbid')
            ->latest('last_login')
            ->first();

        if ($registration) {
            try {
                $server->request("servergroupdelclient sgid={$group->ts_sgid} cldbid={$registration->dbid}");
                Log::info("Removed account {$account->id} from ATC group '{$group->callsign}' (sgid={$group->ts_sgid})");
            } catch (ServerQueryException $e) {
                Log::warning("servergroupdelclient failed for account {$account->id}: {$e->getMessage()}");
            }
        }

        $assignment->delete();

        if ($group->isEmpty()) {
            $this->deleteServerGroup($group, $server);
        }
    }

    public function sync(Server $server): void
    {
        $activeSessions = Atc::online()->isUK()->get()->keyBy('account_id');
        $assignments = AtcGroupAssignment::with('serverGroup', 'account')->get()->keyBy('account_id');

        foreach ($activeSessions as $accountId => $session) {
            $assignment = $assignments->get($accountId);

            if (! $assignment) {
                $this->assign($session->account, $session->callsign, $server);
            } elseif ($assignment->serverGroup->callsign !== $session->callsign) {
                $this->releaseExisting($session->account, $server);
                $this->assign($session->account, $session->callsign, $server);
            }
        }

        foreach ($assignments as $accountId => $assignment) {
            if (! $activeSessions->has($accountId)) {
                $this->releaseExisting($assignment->account, $server);
            }
        }

        $this->pruneEmptyGroups($server);
    }

    public function pruneEmptyGroups(Server $server): int
    {
        $deleted = 0;

        AtcServerGroup::all()->each(function (AtcServerGroup $group) use ($server, &$deleted) {
            if ($group->isEmpty()) {
                $this->deleteServerGroup($group, $server);
                $deleted++;
            }
        });

        return $deleted;
    }

    private function findOrCreateGroup(string $callsign, Server $server): AtcServerGroup
    {
        $group = AtcServerGroup::where('callsign', $callsign)->first();

        if ($group) {
            return $group;
        }

        try {
            $existingSg = $server->serverGroupGetByName($callsign);
            $sgid = $existingSg->getId();
        } catch (ServerQueryException $e) {
            $sgid = $server->serverGroupCreate($callsign);

            // Set the server group to display infront of the username
            $server->request("servergroupaddperm sgid={$sgid} permsid=i_group_show_name_in_tree permvalue=1 permnegated=0 permskip=1");

            Log::info("Created ATC server group '{$callsign}' with sgid={$sgid}");
        }

        try {
            return AtcServerGroup::create(['callsign' => $callsign, 'ts_sgid' => $sgid]);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::info("ATC group '{$callsign}' created by concurrent process, reusing existing.");

            return AtcServerGroup::where('callsign', $callsign)->firstOrFail();
        }
    }

    private function deleteServerGroup(AtcServerGroup $group, Server $server): void
    {
        try {
            $server->request("servergroupdel sgid={$group->ts_sgid} force=1");

            $group->assignments()->delete();
            $group->delete();
            Log::info("Deleted empty ATC server group '{$group->callsign}' (sgid={$group->ts_sgid})");
        } catch (ServerQueryException $e) {
            Log::warning("serverGroupDelete failed for '{$group->callsign}': {$e->getMessage()}");
        }
    }
}
