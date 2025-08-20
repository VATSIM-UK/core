<?php

namespace App\Console\Commands\Members;

use App\Console\Commands\Command;
use App\Models\Cts\ValidationPosition;
use App\Models\Mship\Account;
use App\Repositories\Cts\ExaminerRepository;
use App\Repositories\Cts\MentorRepository;
use App\Repositories\Cts\StudentRepository;
use App\Repositories\Cts\ValidationPositionRepository;
use Illuminate\Support\Collection;
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
        // Sync Mentors
        $this->syncMentorsByRts(12, 35); // Heathrow
        $this->syncMentorsByRts(17, 34); // Enroute
        $this->syncMentorsByRts(18, 33); // Tower
        $this->syncMentorsByRts(19, 47); // Approach
        $this->syncMentorsByCallsign('OBS', 32); // OBS Mentors
        $this->syncMentorsByCallsign('EGKK_GND', 53); // Gatwick Mentors
        $this->syncMentorsByCallsign('TFP', 65); // PTD Flying Programme Mentors
        $this->syncMentorsByCallsign('P1_PPL(A)', Role::findByName('P1 Mentor')->id); // P1 Mentors
        $this->syncMentorsByCallsign('P2_SEIR(A)', Role::findByName('P2 Mentor')->id); // P2 Mentors

        // Sync Students
        $this->syncPilotStudents(55); // Pilot Students
        $this->syncStudentsByPosition('TFP_FLIGHT', Role::findByName('TFP Student')->id); // TFP Students
        $this->syncStudentsByPosition('EGKK_GND', Role::findByName('Gatwick GND Students')->id); // Gatwick Ground Students

        /**
         * If you wish to do multiple positions for the same rule, don't do what I did and separate them into different
         * calls. If you duplicate the role ID it will succeed on the first, but the next one will remove it.
         */
        // Heathrow students
        $this->syncStudentsByPositions(['EGLL_2_GND', 'EGLL_S_TWR', 'EGLL_N_APP'], Role::findByName('ATC Students (Heathrow)')->id);
        // OBS Student
        $this->syncStudentsByPositions(['OBS_CC_PT2', 'OBS_NX_PT2', 'OBS_PH_PT2', 'OBS_SS_PT2'], Role::findByName('OBS Students')->id);

        $this->syncStudentsByRts(18, Role::findByName('ATC Students (TWR)')->id); // TWR Students
        $this->syncStudentsByRts(19, Role::findByName('ATC Students (APP)')->id); // APP Students
        $this->syncStudentsByRts(17, Role::findByName('ATC Students (ENR)')->id); // Enroute Students

        // Sync Examiners
        // Split ATC Examiner roles per level
        $this->syncObsExaminers(Role::findByName('ATC Examiner (OBS)')->id);
        $this->syncTwrExaminers(Role::findByName('ATC Examiner (TWR)')->id);
        $this->syncAppExaminers(Role::findByName('ATC Examiner (APP)')->id);
        $this->syncCtrExaminers(Role::findByName('ATC Examiner (CTR)')->id);

        $this->syncPilotExaminers(40); // Pilot
    }

    private function syncMentorsByRts(int $rtsId, int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new MentorRepository)->getMentorsWithin($rtsId);
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncMentorsByCallsign(string $search, int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new MentorRepository)->getMentorsFor($search);
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncObsExaminers(int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new ExaminerRepository)->getObsExaminers();
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncTwrExaminers(int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new ExaminerRepository)->getTwrExaminers();
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncAppExaminers(int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new ExaminerRepository)->getAppExaminers();
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncCtrExaminers(int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new ExaminerRepository)->getCtrExaminers();
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncPilotExaminers(int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new ExaminerRepository)->getPilotExaminers();
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncPilotStudents(int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new StudentRepository)->getStudentsWithin(13);
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncStudentsByPosition(string $callsign, int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new StudentRepository)->getStudentsWithRequestPermissionsFor($callsign);
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncStudentsByPositions(array $callsigns, int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = collect();
        foreach ($callsigns as $callsign) {
            $shouldHaveRole = $shouldHaveRole->merge((new StudentRepository)->getStudentsWithRequestPermissionsFor($callsign));
        }
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncStudentsByRts(int $rtsId, int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new StudentRepository)->getStudentsWithin($rtsId);
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncValidatedMembers(ValidationPosition $validationPosition, int $roleId): void
    {
        $hasRole = $this->getAccountsWithRoleId($roleId);
        $shouldHaveRole = (new ValidationPositionRepository)->getValidatedMembersFor($validationPosition)->map(fn ($item) => $item['id']);
        $this->syncRoles($hasRole, $shouldHaveRole, $roleId);
    }

    private function syncRoles(Collection $hasRole, Collection $shouldHaveRole, $roleId): void
    {
        // Users that have the role, but should not have the role
        $removeRole = $hasRole->filter(function ($value) use ($shouldHaveRole) {
            return ! $shouldHaveRole->contains($value);
        })->all();

        // Users that should have the role, but do not have the role
        $assignRole = $shouldHaveRole->filter(function ($value) use ($hasRole) {
            return ! $hasRole->contains($value);
        })->all();

        foreach ($assignRole as $account) {
            Account::find($account)->assignRole($roleId);
        }

        foreach ($removeRole as $account) {
            Account::find($account)->removeRole($roleId);
        }
    }

    private function getAccountsWithRoleId(int $roleId): Collection
    {
        return Role::findById($roleId)->users()->pluck('id');
    }
}
