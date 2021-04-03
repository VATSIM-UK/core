<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExaminerRepository;
use App\Repositories\Cts\MembershipRepository;
use App\Repositories\Cts\MentorRepository;
use App\Repositories\Cts\StudentRepository;
use Spatie\Permission\Models\Role;

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

        $this->syncPilotStudents(); // Pilot TG Members ????

        $this->syncAtcExaminers(31);
        $this->syncPilotExaminers(40);
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

    private function syncAtcExaminers($roleId)
    {
        $examiners = (new ExaminerRepository)->getAtcExaminers();

        $role = Role::findById($roleId)->users()->pluck('id');

        // Users that have the role, but are not examiners
        $removeRole = $role->filter(function ($value) use ($examiners) {
            return ! $examiners->contains($value);
        })->all();

        // Users that are examiners, but do not have the role
        $assignRole = $examiners->filter(function ($value) use ($role) {
            return ! $role->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole($roleId);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole($roleId);
        }
    }

    private function syncPilotExaminers($roleId)
    {
        $examiners = (new ExaminerRepository)->getPilotExaminers();

        $role = Role::findById($roleId)->users()->pluck('id');

        // Users that have the role, but are not examiners
        $removeRole = $role->filter(function ($value) use ($examiners) {
            return ! $examiners->contains($value);
        })->all();

        // Users that are examiners, but do not have the role
        $assignRole = $examiners->filter(function ($value) use ($role) {
            return ! $role->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole($roleId);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole($roleId);
        }
    }

    private function syncPilotStudents()
    {
        $students = (new StudentRepository)->getStudentsWithin(13);

        $role = Role::findById(63)->users()->pluck('id');

        // Users that have the role, but are not members
        $removeRole = $role->filter(function ($value) use ($students) {
            return ! $students->contains($value);
        })->all();

        // Users that are members, but do not have the role
        $assignRole = $students->filter(function ($value) use ($role) {
            return ! $role->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole(63);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole(63);
        }
    }
}
