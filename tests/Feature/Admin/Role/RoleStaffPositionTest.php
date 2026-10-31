<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Role;

use App\Filament\Admin\Resources\Roles\Pages\CreateRole;
use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Models\Mship\RoleStaffPosition;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class RoleStaffPositionTest extends BaseAdminTestCase
{
    #[Test]
    public function it_requires_department_and_job_title_together(): void
    {
        $this->actingAsAdminUser(['role.view.*', 'role.create']);

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Incomplete Staff Role',
                'guard_name' => 'web',
                'staff_department' => RoleStaffPosition::ATC_TRAINING_DEPARTMENT,
                'staff_job_title' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['staff_job_title' => 'required_with']);

        $this->assertDatabaseMissing('mship_role', [
            'name' => 'Incomplete Staff Role',
        ]);
    }

    #[Test]
    public function it_creates_updates_and_clears_the_staff_position(): void
    {
        $this->actingAsAdminUser(['role.view.*', 'role.create', 'role.edit.*']);

        Livewire::test(CreateRole::class)
            ->fillForm([
                'name' => 'Staff Position Role',
                'guard_name' => 'web',
                'staff_department' => RoleStaffPosition::ATC_TRAINING_DEPARTMENT,
                'staff_job_title' => 'ATC Training Director',
                'staff_is_department_lead' => true,
                'staff_sort_order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $role = Role::query()->where('name', 'Staff Position Role')->firstOrFail();

        $this->assertDatabaseHas('mship_role_staff_position', [
            'role_id' => $role->id,
            'department' => RoleStaffPosition::ATC_TRAINING_DEPARTMENT,
            'job_title' => 'ATC Training Director',
            'is_department_lead' => true,
            'sort_order' => 1,
        ]);

        Livewire::test(EditRole::class, ['record' => $role->id])
            ->assertFormSet([
                'staff_department' => RoleStaffPosition::ATC_TRAINING_DEPARTMENT,
                'staff_job_title' => 'ATC Training Director',
                'staff_is_department_lead' => true,
                'staff_sort_order' => 1,
            ])
            ->fillForm([
                'staff_department' => RoleStaffPosition::TECHNOLOGY_DEPARTMENT,
                'staff_job_title' => 'Developer',
                'staff_is_department_lead' => false,
                'staff_sort_order' => 4,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('mship_role_staff_position', [
            'role_id' => $role->id,
            'department' => RoleStaffPosition::TECHNOLOGY_DEPARTMENT,
            'job_title' => 'Developer',
            'is_department_lead' => false,
            'sort_order' => 4,
        ]);

        Livewire::test(EditRole::class, ['record' => $role->id])
            ->fillForm([
                'staff_department' => null,
                'staff_job_title' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseMissing('mship_role_staff_position', [
            'role_id' => $role->id,
        ]);
    }
}
