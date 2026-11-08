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
    public function it_lists_atc_training_pilot_training_technology_and_community_positions_from_roles(): void
    {
        $ada = $this->member('Ada', 'Arkley', 'Addie');
        $eve = $this->member('Eve', 'Shared');
        $ben = $this->member('Ben', 'Team');
        $cara = $this->member('Cara', 'Both');
        $dan = $this->member('Dan', 'Tech');
        $pia = $this->member('Pia', 'Director');
        $sam = $this->member('Sam', 'Instructor');
        $wren = $this->member('Wren', 'Community');
        $kit = $this->member('Kit', 'Outreach');

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

        $pia->assignRole(
            $this->position('Pilot Training Director Role', RoleStaffPosition::PILOT_TRAINING_DEPARTMENT, 'Pilot Training Director', lead: true, sort: 0),
        );
        $sam->assignRole(
            $this->position('Pilot Training Manager Role', RoleStaffPosition::PILOT_TRAINING_DEPARTMENT, 'Pilot Training Manager', sort: 10),
        );
        $this->position('Pilot Empty Seat Role', RoleStaffPosition::PILOT_TRAINING_DEPARTMENT, 'Pilot Empty Seat', sort: 20);

        $wren->assignRole(
            $this->position('Community Director Role', RoleStaffPosition::COMMUNITY_DEPARTMENT, 'Community Director', lead: true, sort: 0),
        );
        $kit->assignRole(
            $this->position('Outreach Assistant Role', RoleStaffPosition::COMMUNITY_DEPARTMENT, 'Outreach Assistant', sort: 10),
        );
        $this->position('Community Empty Seat Role', RoleStaffPosition::COMMUNITY_DEPARTMENT, 'Community Empty Seat', sort: 20);

        $this->position('Secret Operations Role', 'Operations', 'Secret Operations Title', sort: 0);

        $response = $this->actingAs($this->user)->get(route('site.staff'));

        $response->assertOk();
        $response->assertSeeInOrder([
            '&thinsp; Management',
            '&thinsp; Operations',
            '&thinsp; ATC Training',
            '&thinsp; Pilot Training',
            '&thinsp; Technology',
            '&thinsp; Community',
        ], false);
        $response->assertSeeInOrder([
            'Addie Arkley',
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
            'Pia Director',
            'Pilot Training Director',
            'Sam Instructor',
            'Pilot Training Manager',
            'Dan Tech',
            'Technology Director',
            'Technology Side Title',
            'Cara Both',
            'Technology Developer',
            'Wren Community',
            'Community Director',
            'Kit Outreach',
            'Outreach Assistant',
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
        $response->assertDontSee('Pilot Empty Seat');
        $response->assertDontSee('Community Empty Seat');
        $response->assertDontSee('William Shaw');
        $response->assertDontSee('Callum Hicks');
        $response->assertDontSee('Ben Carpenter');
        $response->assertDontSee('Lewis Hammett');

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, 'staff-management container'));
        $this->assertSame(3, substr_count($html, 'staff-departments__row row'));
        $this->assertSame(5, substr_count($html, 'staff-departments__department col-md-6'));
        $response->assertDontSee('Ada Arkley');
        $this->assertSame(1, substr_count($html, 'Addie Arkley'));
        $this->assertSame(1, substr_count($html, 'Ben Team'));
        $this->assertSame(1, substr_count($html, 'Dan Tech'));
        $this->assertSame(2, substr_count($html, 'Cara Both'));
        $this->assertSame(1, substr_count($html, 'Pia Director'));
        $this->assertSame(1, substr_count($html, 'Sam Instructor'));
        $this->assertSame(1, substr_count($html, 'Wren Community'));
        $this->assertSame(1, substr_count($html, 'Kit Outreach'));
    }

    private function member(string $first, string $last, ?string $nickname = null): Account
    {
        return Account::factory()->createQuietly([
            'name_first' => $first,
            'name_last' => $last,
            'nickname' => $nickname,
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
