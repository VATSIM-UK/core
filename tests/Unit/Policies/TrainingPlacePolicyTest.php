<?php

namespace Tests\Unit\Policies;

use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\WaitingList;
use App\Policies\TrainingPlacePolicy;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrainingPlacePolicyTest extends TestCase
{
    use DatabaseTransactions;

    private TrainingPlacePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = app(TrainingPlacePolicy::class);
    }

    #[Test]
    public function create_adhoc_with_atc_permission_allows_atc_department_via_gate(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo('training-places.create-adhoc.atc');

        $this->assertTrue($account->can('createAdhoc', [TrainingPlace::class, WaitingList::ATC_DEPARTMENT]));
        $this->assertFalse($account->can('createAdhoc', [TrainingPlace::class, WaitingList::PILOT_DEPARTMENT]));
    }

    #[Test]
    public function create_adhoc_with_pilot_permission_allows_pilot_department_via_gate(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo('training-places.create-adhoc.pilot');

        $this->assertTrue($account->can('createAdhoc', [TrainingPlace::class, WaitingList::PILOT_DEPARTMENT]));
        $this->assertFalse($account->can('createAdhoc', [TrainingPlace::class, WaitingList::ATC_DEPARTMENT]));
    }

    #[Test]
    public function create_adhoc_with_bare_permission_allows_either_department(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo('training-places.create-adhoc');

        $this->assertTrue($account->can('createAdhoc', [TrainingPlace::class, WaitingList::ATC_DEPARTMENT]));
        $this->assertTrue($account->can('createAdhoc', [TrainingPlace::class, WaitingList::PILOT_DEPARTMENT]));
        $this->assertTrue($this->policy->createAdhoc($account));
    }

    #[Test]
    public function create_adhoc_without_permission_denies(): void
    {
        $account = Account::factory()->create();

        $this->assertFalse($account->can('createAdhoc', [TrainingPlace::class, WaitingList::ATC_DEPARTMENT]));
        $this->assertFalse($account->can('createAdhoc', TrainingPlace::class));
    }
}
