<?php

declare(strict_types=1);

namespace Database\Seeders\LocalDevelopment\Training;

use Illuminate\Database\Seeder;
use RuntimeException;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;

/**
 * Defines Spatie roles and permissions for local development training personas.
 *
 * These roles are not production equivalents — authorisation in the app is permission-based.
 * See database/seeders/LocalDevelopment/README.md#dev-roles-vs-production.
 *
 * @see DevTrainingPersonas
 */
class DevTrainingRolesSeeder extends Seeder
{
    public function run(): void
    {
        app()['cache']->forget('spatie.permission.cache');

        $staffRole = Role::firstOrCreate([
            'name' => DevTrainingPersonas::STAFF_ROLE,
            'guard_name' => 'web',
        ], [
            'default' => false,
        ]);
        $this->syncRolePermissions($staffRole, DevTrainingPersonas::STAFF_PERMISSIONS);

        $studentRole = Role::firstOrCreate([
            'name' => DevTrainingPersonas::STUDENT_ROLE,
            'guard_name' => 'web',
        ], [
            'default' => false,
        ]);
        $this->syncRolePermissions($studentRole, DevTrainingPersonas::STUDENT_PERMISSIONS);

        $this->command?->info('Dev training staff and student roles configured.');
    }

    /**
     * @param  list<string>  $permissions
     */
    private function syncRolePermissions(Role $role, array $permissions): void
    {
        try {
            $role->syncPermissions($permissions);
        } catch (PermissionDoesNotExist $exception) {
            throw new RuntimeException(
                'Training permissions are missing. Run `php artisan db:seed` (RolesAndPermissionsSeeder) before seeding local development training data.',
                previous: $exception,
            );
        }
    }
}
