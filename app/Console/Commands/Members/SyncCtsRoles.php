<?php

namespace App\Console\Commands\Members;

use App\Models\Mship\Account;
use App\Console\Commands\Command;
use App\Repositories\Cts\MembershipRepository;
use Spatie\Permission\Models\Role;
use App\Repositories\Cts\MentorRepository;

class SyncCtsRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:cts-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allocate the relevant roles on Core depending on CTS permissions.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->syncMentorsByRts(12, 35); // Heathrow
        $this->syncMentorsByRts(13, 42); // Pilot
        $this->syncMentorsByRts(17, 34); // Enroute
        $this->syncMentorsByRts(18, 33); // Tower
        $this->syncMentorsByRts(19, 47); // Approach

        $this->syncMentorsByCallsign('OBS', 32); // OBS Mentors
        $this->syncMentorsByCallsign('EGKK_GND', 53); // Gatwick Mentors

        $this->syncRtsMembers(13, 63); // Pilot TG Members
    }

    private function syncMentorsByRts($rtsId, $roleId)
    {
        $mentors = (new MentorRepository)->getMentorsWithin($rtsId);

        $role = Role::findById($roleId)->users()->pluck('id');

        // Users that have the role, but are not mentors
        $removeRole = $role->filter(function ($value) use ($mentors) {
            return ! $mentors->contains($value);
        })->all();

        // Users that are mentors, but do not have the role
        $assignRole = $mentors->filter(function ($value) use ($role) {
            return ! $role->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole($roleId);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole($roleId);
        }
    }

    private function syncMentorsByCallsign($search, $roleId)
    {
        $mentors = (new MentorRepository)->getMentorsFor($search);

        $role = Role::findById($roleId)->users()->pluck('id');

        // Users that have the role, but are not mentors
        $removeRole = $role->filter(function ($value) use ($mentors) {
            return ! $mentors->contains($value);
        })->all();

        // Users that are mentors, but do not have the role
        $assignRole = $mentors->filter(function ($value) use ($role) {
            return ! $role->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole($roleId);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole($roleId);
        }
    }

    private function syncRtsMembers($rtsId, $roleId)
    {
        $members = (new MembershipRepository)->getMembersOf($rtsId)->pluck('cid');

        $role = Role::findById($roleId)->users()->pluck('id');

        // Users that have the role, but are not mentors
        $removeRole = $role->filter(function ($value) use ($members) {
            return ! $members->contains($value);
        })->all();

        // Users that are mentors, but do not have the role
        $assignRole = $members->filter(function ($value) use ($role) {
            return ! $role->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole($roleId);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole($roleId);
        }
    }
}
