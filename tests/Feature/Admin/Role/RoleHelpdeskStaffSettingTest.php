<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Role;

use App\Filament\Admin\Resources\Roles\Pages\CreateRole;
use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Services\Helpdesk\HelpdeskStaffOptions;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class RoleHelpdeskStaffSettingTest extends BaseAdminTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        $this->mock(HelpdeskStaffOptions::class, function (MockInterface $mock): void {
            $mock->shouldReceive('departments')->andReturn([10 => 'Primary', 40 => 'Extended']);
            $mock->shouldReceive('teams')->andReturn([20 => 'Team']);
            $mock->shouldReceive('roles')->andReturn([30 => 'Primary role', 50 => 'Extended role']);
        });
    }

    #[Test]
    public function it_creates_loads_and_removes_a_helpdesk_staff_mapping(): void
    {
        $this->actingAsAdminUser(['role.view.*', 'role.create', 'role.edit.*']);

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Helpdesk Staff Role',
                'guard_name' => 'web',
                'helpdesk_enabled' => true,
                'helpdesk_department_id' => 10,
                'helpdesk_team_id' => 20,
                'helpdesk_role_id' => 30,
                'helpdesk_priority' => 100,
                'helpdesk_extended_access' => [
                    ['department_id' => 40, 'role_id' => 50],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::query()->where('name', 'Helpdesk Staff Role')->firstOrFail();

        $this->assertDatabaseHas('mship_role_helpdesk_staff_setting', [
            'role_id' => $role->id,
            'helpdesk_department_id' => 10,
            'helpdesk_team_id' => 20,
            'helpdesk_role_id' => 30,
            'priority' => 100,
        ]);
        $this->assertDatabaseHas('mship_role_helpdesk_staff_access', [
            'helpdesk_department_id' => 40,
            'helpdesk_role_id' => 50,
        ]);

        Livewire::test(EditRole::class, ['record' => $role->id])
            ->assertFormSet([
                'helpdesk_enabled' => true,
                'helpdesk_department_id' => 10,
                'helpdesk_team_id' => 20,
                'helpdesk_role_id' => 30,
                'helpdesk_priority' => 100,
            ])
            ->fillForm(['helpdesk_enabled' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('mship_role_helpdesk_staff_setting', [
            'role_id' => $role->id,
        ]);
    }
}
