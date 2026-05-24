<?php

namespace Tests\Unit\Policies;

use App\Models\Mship\Account;
use App\Policies\PasswordPolicy;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    protected PasswordPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PasswordPolicy;
    }

    #[Test]
    public function it_allows_setup_during_login_when_mandatory_password_is_missing(): void
    {
        $account = Account::factory()->create(['password' => null]);
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $account->assignRole($role);

        $this->assertTrue($this->policy->setupDuringLogin($account->fresh()));
    }

    #[Test]
    public function it_denies_setup_during_login_when_password_already_exists(): void
    {
        $account = Account::factory()->create(['password' => 'Secret123']);
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $account->assignRole($role);

        $this->assertFalse($this->policy->setupDuringLogin($account->fresh()));
    }
}
