<?php

namespace Tests\Feature\Admin;

use App\Models\Mship\Account;
use Illuminate\Support\Arr;
use Livewire\Livewire;
use Mockery\MockInterface;
use Tests\TestCase;

abstract class BaseAdminTestCase extends TestCase
{
    protected Account $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = Account::factory()->create();
        $this->adminUser->givePermissionTo('admin.access');
    }

    /**
     * Sets the current auth user to be a user with admin panel access
     *
     * @param  string|string[]  $permissions
     * @return void
     */
    protected function actingAsAdminUser(mixed $permissions = [])
    {
        $this->actingAs($this->adminUser);

        foreach (Arr::wrap($permissions) as $permission) {
            $this->adminUser->givePermissionTo($permission);
        }
    }

    protected function actingAsSuperUser()
    {
        $this->actingAs($this->privacc);
    }

    protected function mockPolicyAction(string $policyClass, string $policyAction, bool $response = true)
    {
        $this->partialMock($policyClass, fn (MockInterface $mock) => $mock->shouldReceive($policyAction)->andReturn($response));
    }

    protected function assertActionDependentOnPolicy(string $pageClass, string $actionName, string $policyClass, ?string $policyAction, ?int $recordId)
    {
        // Login as a user with access to the panel first
        Livewire::actingAs($this->privacc);

        $policyActionName = $policyAction ?? $actionName;

        $this->mockPolicyAction($policyClass, $policyActionName, false);
        Livewire::test($pageClass, ['record' => $recordId])->assertActionHidden($actionName);

        $this->mockPolicyAction($policyClass, $policyActionName, true);
        Livewire::test($pageClass, ['record' => $recordId])->assertActionVisible($actionName);
    }
}
