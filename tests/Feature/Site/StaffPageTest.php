<?php

declare(strict_types=1);

namespace Tests\Feature\Site;

use App\Models\Mship\Account;
use App\Models\Mship\RoleStaffPosition;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StaffPageTest extends TestCase
{
    #[Test]
    public function it_lists_atc_training_and_technology_positions_from_roles(): void
    {
        $ada = $this->member('Ada', 'Arkley');
        $eve = $this->member('Eve', 'Shared');
        $ben = $this->member('Ben', 'Team');
        $cara = $this->member('Cara', 'Both');
        $dan = $this->member('Dan', 'Tech');

        $ada->assignRole(
            $this->position('ATC Training Director Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, 'ATC Training Director', lead: true, sort: 0),
            $this->position('ATC Stackable Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, "Stackable\nTitle", sort: 10),
        );

        $this->position('ATC Absent Director Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, 'ATC Absent Director', lead: true, sort: 2);

        $assistant = $this->position('ATC Assistant One Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, 'ATC Assistant One', sort: 20);
        $eve->assignRole($assistant);
        $ben->assignRole(
            $assistant,
            $this->position('ATC Assistant Two Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, 'ATC Assistant Two', sort: 30),
        );

        $this->position('ATC Empty Seat Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, 'ATC Empty Seat', sort: 40);

        $cara->assignRole(
            $this->position('ATC Cross Department Role', RoleStaffPosition::ATC_TRAINING_DEPARTMENT, 'ATC Cross Department', sort: 50),
            $this->position('Technology Developer Role', RoleStaffPosition::TECHNOLOGY_DEPARTMENT, 'Technology Developer', sort: 10),
        );

        $dan->assignRole(
            $this->position('Technology Director Role', RoleStaffPosition::TECHNOLOGY_DEPARTMENT, 'Technology Director', lead: true, sort: 0),
            $this->position('Technology Side Title Role', RoleStaffPosition::TECHNOLOGY_DEPARTMENT, 'Technology Side Title', sort: 5),
        );

        $this->position('Secret Operations Role', 'Operations', 'Secret Operations Title', sort: 0);

        $response = $this->actingAs($this->user)->get(route('site.staff'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Ada Arkley',
            'ATC Training Director',
            'Stackable',
            'Staff members',
            'Eve Shared',
            'ATC Assistant One',
            'Ben Team',
            'ATC Assistant One',
            'ATC Assistant Two',
            'Cara Both',
            'ATC Cross Department',
            'Ben Carpenter',
            'Pilot Training Director',
            'Dan Tech',
            'Technology Director',
            'Technology Side Title',
            'Cara Both',
            'Technology Developer',
        ]);
        $response->assertSee('Stackable<br />', false);
        $response->assertSee('staff-directory__lead');
        $response->assertSee('staff-directory__member');
        $response->assertSee('>AA</div>', false);
        $response->assertSee('>—</div>', false);
        $response->assertDontSee('Department lead');
        $response->assertDontSee('Secret Operations Title');
        $response->assertDontSee('ATC Absent Director');
        $response->assertDontSee('ATC Empty Seat');

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'Ada Arkley'));
        $this->assertSame(1, substr_count($html, 'Ben Team'));
        $this->assertSame(1, substr_count($html, 'Dan Tech'));
        $this->assertSame(2, substr_count($html, 'Cara Both'));
    }

    private function member(string $first, string $last): Account
    {
        return Account::factory()->createQuietly([
            'name_first' => $first,
            'name_last' => $last,
        ]);
    }

    private function position(string $roleName, string $department, string $jobTitle, bool $lead = false, int $sort = 0): Role
    {
        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        RoleStaffPosition::create([
            'role_id' => $role->id,
            'department' => $department,
            'job_title' => $jobTitle,
            'is_department_lead' => $lead,
            'sort_order' => $sort,
        ]);

        return $role;
    }
}
