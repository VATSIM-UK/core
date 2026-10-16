<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Discord;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DiscordRoleRulePermissionsTest extends TestCase
{
    public static function permissionProvider(): array
    {
        return [
            'view' => ['discord.role-rule.view'],
            'edit' => ['discord.role-rule.edit'],
        ];
    }

    #[Test]
    #[DataProvider('permissionProvider')]
    public function the_seeded_permissions_exist(string $permission): void
    {
        $this->assertTrue(
            Permission::where('name', $permission)->where('guard_name', 'web')->exists(),
            "Expected permission {$permission} to be seeded."
        );
    }
}
