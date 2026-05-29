<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Account;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MandatoryPasswordSetupTest extends TestCase
{
    #[Test]
    public function it_requires_mandatory_password_setup_when_role_requires_password_and_none_is_set(): void
    {
        $account = Account::factory()->create(['password' => null]);
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $account->assignRole($role);

        $this->assertTrue($account->fresh()->requiresMandatoryPasswordSetup());
    }

    #[Test]
    public function it_does_not_require_mandatory_password_setup_when_password_exists(): void
    {
        $account = Account::factory()->create(['password' => 'Secret123']);
        $role = factory(Role::class)->create(['password_mandatory' => true]);
        $account->assignRole($role);

        $this->assertFalse($account->fresh()->requiresMandatoryPasswordSetup());
    }

    #[Test]
    public function it_does_not_require_mandatory_password_setup_when_password_is_not_mandatory(): void
    {
        $account = Account::factory()->create(['password' => null]);

        $this->assertFalse($account->requiresMandatoryPasswordSetup());
    }
}
