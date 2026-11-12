<?php

namespace Tests\Feature\Training;

use App\Models\Mship\Account;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PrefixWaitingListAndTrainingPlacePermissionsTest extends TestCase
{
    #[Test]
    public function it_prefixes_permission_names_without_dropping_grants(): void
    {
        $role = factory(Role::class)->create(['name' => 'prefix-migration-role']);
        $otherRole = factory(Role::class)->create(['name' => 'prefix-migration-other-role']);
        $account = Account::factory()->create();
        $wildcardAccount = Account::factory()->create();

        foreach ([
            'training.waiting-lists.view.atc',
            'training.waiting-lists.view.12',
            'training.waiting-lists.view.*',
            'training.training-places.view.atc',
        ] as $newName) {
            $this->deletePermission($newName);
        }

        $viewAtc = $this->permission('waiting-lists.view.atc');
        $viewList = $this->permission('waiting-lists.view.12');
        $viewWildcard = $this->permission('waiting-lists.view.*');
        $placesView = $this->permission('training-places.view.atc');
        $existingAccess = $this->permission('training.waiting-lists.access');
        $oldAccess = $this->permission('waiting-lists.access');

        $role->givePermissionTo([$viewAtc, $viewList, $viewWildcard, $placesView, $oldAccess, $existingAccess]);
        $otherRole->givePermissionTo($existingAccess);
        $account->givePermissionTo([$viewAtc, $viewList, $placesView, $oldAccess]);
        $wildcardAccount->givePermissionTo($viewWildcard);

        $this->runMigration();

        $this->assertSame('training.waiting-lists.view.atc', $this->permissionName($viewAtc->id));
        $this->assertSame('training.waiting-lists.view.12', $this->permissionName($viewList->id));
        $this->assertSame('training.waiting-lists.view.*', $this->permissionName($viewWildcard->id));
        $this->assertSame('training.training-places.view.atc', $this->permissionName($placesView->id));
        $this->assertDatabaseMissing('mship_permission', ['id' => $oldAccess->id]);
        $this->assertSame(1, Permission::query()->where('name', 'training.waiting-lists.access')->where('guard_name', 'web')->count());

        $this->assertDatabaseHas('mship_role_permission', [
            'role_id' => $role->id,
            'permission_id' => $viewAtc->id,
        ]);
        $this->assertDatabaseHas('mship_account_permission', [
            'model_id' => $account->id,
            'permission_id' => $viewList->id,
        ]);
        $this->assertSame(1, DB::table('mship_role_permission')->where('role_id', $role->id)->where('permission_id', $existingAccess->id)->count());
        $this->assertDatabaseHas('mship_account_permission', [
            'model_id' => $account->id,
            'permission_id' => $existingAccess->id,
        ]);

        $this->forgetPermissionCache();

        $role = $role->fresh();
        $otherRole = $otherRole->fresh();
        $account = $account->fresh();
        $wildcardAccount = $wildcardAccount->fresh();

        $this->assertTrue($role->hasPermissionTo('training.waiting-lists.view.atc'));
        $this->assertTrue($account->hasPermissionTo('training.waiting-lists.view.12'));
        $this->assertTrue($account->hasPermissionTo('training.training-places.view.atc'));
        $this->assertTrue($account->hasPermissionTo('training.waiting-lists.access'));
        $this->assertTrue($role->hasPermissionTo('training.waiting-lists.access'));
        $this->assertTrue($otherRole->hasPermissionTo('training.waiting-lists.access'));
        $this->assertTrue($wildcardAccount->hasPermissionTo('training.waiting-lists.view.*'));
        $this->assertTrue($wildcardAccount->can('training.waiting-lists.view.atc'));

        $snapshot = $this->prefixedPermissionNames();

        $this->runMigration();

        $this->assertSame($snapshot, $this->prefixedPermissionNames());
        $this->assertSame(0, DB::table('mship_permission')->where(fn ($query) => $this->whereLegacyName($query))->count());
    }

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_11_08_000000_prefix_waiting_list_and_training_place_permissions.php');
        $migration->up();
    }

    private function permission(string $name): Permission
    {
        return Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]);
    }

    private function deletePermission(string $name): void
    {
        $ids = DB::table('mship_permission')->where('name', $name)->where('guard_name', 'web')->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('mship_role_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('mship_account_permission')->whereIn('permission_id', $ids)->delete();
        DB::table('mship_permission')->whereIn('id', $ids)->delete();
        $this->forgetPermissionCache();
    }

    private function permissionName(int $id): ?string
    {
        return DB::table('mship_permission')->where('id', $id)->value('name');
    }

    /**
     * @return array<int, string>
     */
    private function prefixedPermissionNames(): array
    {
        return DB::table('mship_permission')
            ->where('name', 'like', 'training.waiting-lists%')
            ->orWhere('name', 'like', 'training.training-places%')
            ->orderBy('id')
            ->pluck('name', 'id')
            ->all();
    }

    private function whereLegacyName($query): void
    {
        $query->where('name', 'waiting-lists')
            ->orWhere('name', 'like', 'waiting-lists.%')
            ->orWhere('name', 'training-places')
            ->orWhere('name', 'like', 'training-places.%');
    }

    private function forgetPermissionCache(): void
    {
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
