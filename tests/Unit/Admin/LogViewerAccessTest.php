<?php

namespace Tests\Unit\Admin;

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LogViewerAccessTest extends TestCase
{
    #[Test]
    public function it_allows_user_with_log_viewer_role()
    {
        $user = Account::factory()->create();

        $role = Role::create(['name' => 'log-viewer-test-role', 'guard_name' => 'web']);
        $role->givePermissionTo('log-viewer.access');
        $user->assignRole($role);

        $this->assertTrue(
            Gate::forUser($user)->allows('viewLogViewer')
        );
    }

    #[Test]
    public function it_denies_user_without_permission()
    {
        $user = $this->user;

        $this->assertFalse(
            Gate::forUser($user)->allows('viewLogViewer')
        );
    }
}
