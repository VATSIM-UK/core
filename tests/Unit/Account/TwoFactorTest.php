<?php

namespace Tests\Unit\Account;

use App\Models\Mship\Account;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    protected Account $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = Account::factory()->create();
    }

    #[Test]
    public function it_determines_that_two_factor_is_not_mandatory(): void
    {
        $this->assertFalse($this->user->mandatory_two_factor);
    }

    #[Test]
    public function it_determines_that_two_factor_is_mandatory(): void
    {
        $role = factory(Role::class)->create(['two_factor_mandatory' => true]);

        $this->user->assignRole($role);

        $this->assertTrue($this->user->fresh()->mandatory_two_factor);
    }

    #[Test]
    public function it_requires_two_factor_setup_when_mandatory_and_not_enabled(): void
    {
        $role = factory(Role::class)->create(['two_factor_mandatory' => true]);
        $this->user->assignRole($role);

        $this->assertTrue($this->user->fresh()->requiresTwoFactorSetup());
    }

    #[Test]
    public function it_does_not_require_two_factor_setup_when_enabled(): void
    {
        $role = factory(Role::class)->create(['two_factor_mandatory' => true]);
        $this->user->assignRole($role);

        app(EnableTwoFactorAuthentication::class)($this->user->fresh(), true);
        $this->user->fresh()->forceFill(['two_factor_confirmed_at' => now()])->save();

        $this->assertFalse($this->user->fresh()->requiresTwoFactorSetup());
        $this->assertTrue($this->user->fresh()->requiresTwoFactorChallenge());
    }
}
