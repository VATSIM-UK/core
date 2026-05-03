<?php

namespace Tests\Feature\Admin\Role;

use App\Filament\Admin\Resources\Roles\Pages\ListRoles;
use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Policies\RolePolicy;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class RoleResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = RoleResource::class;

    protected ?string $policy = RolePolicy::class;

    #[Test]
    public function it_hides_sync_discord_table_action_without_role_sync_discord_permission(): void
    {
        $role = Role::create(['name' => 'Role For Sync Discord Visibility', 'guard_name' => 'web']);

        $this->actingAsAdminUser(['role.view.*']);

        Livewire::test(ListRoles::class)
            ->assertSuccessful()
            ->assertTableActionHidden('syncDiscord', $role);
    }

    #[Test]
    public function it_shows_sync_discord_table_action_with_role_sync_discord_permission(): void
    {
        $role = Role::create(['name' => 'Role For Sync Discord Visible', 'guard_name' => 'web']);

        $this->actingAsAdminUser(['role.view.*', 'role.sync-discord.*']);

        Livewire::test(ListRoles::class)
            ->assertSuccessful()
            ->assertTableActionVisible('syncDiscord', $role);
    }
}
