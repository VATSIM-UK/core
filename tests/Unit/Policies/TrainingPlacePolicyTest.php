<?php

namespace Tests\Unit\Policies;

use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
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
    public function view_any_allows_when_user_has_atc_or_pilot_view_permission(): void
    {
        $atcOnly = Account::factory()->create();
        $atcOnly->givePermissionTo('training-places.view.atc');

        $pilotOnly = Account::factory()->create();
        $pilotOnly->givePermissionTo('training-places.view.pilot');

        $neither = Account::factory()->create();

        $this->assertTrue($this->policy->viewAny($atcOnly));
        $this->assertTrue($this->policy->viewAny($pilotOnly));
        $this->assertFalse($this->policy->viewAny($neither));
    }

    #[Test]
    public function view_any_allows_wildcard_view_permission(): void
    {
        $account = Account::factory()->create();
        $account->givePermissionTo('training-places.view.*');

        $this->assertTrue($this->policy->viewAny($account));
    }

    #[Test]
    public function view_respects_department_permissions(): void
    {
        $atcPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forTrainingPosition(TrainingPosition::factory()->create())
            ->create());

        $qualification = Qualification::firstWhere('code', 'PPL')
            ?? Qualification::factory()->create(['code' => 'PPL', 'type' => 'pilot']);

        $pilotPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forQualification($qualification)
            ->create());

        $atcOnly = Account::factory()->create();
        $atcOnly->givePermissionTo('training-places.view.atc');

        $pilotOnly = Account::factory()->create();
        $pilotOnly->givePermissionTo('training-places.view.pilot');

        $this->assertTrue($this->policy->view($atcOnly, $atcPlace));
        $this->assertFalse($this->policy->view($atcOnly, $pilotPlace));

        $this->assertTrue($this->policy->view($pilotOnly, $pilotPlace));
        $this->assertFalse($this->policy->view($pilotOnly, $atcPlace));
    }

    #[Test]
    public function view_allows_wildcard_for_either_department(): void
    {
        $atcPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forTrainingPosition(TrainingPosition::factory()->create())
            ->create());

        $qualification = Qualification::firstWhere('code', 'PPL')
            ?? Qualification::factory()->create(['code' => 'PPL', 'type' => 'pilot']);

        $pilotPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forQualification($qualification)
            ->create());

        $account = Account::factory()->create();
        $account->givePermissionTo('training-places.view.*');

        $this->assertTrue($this->policy->view($account, $atcPlace));
        $this->assertTrue($this->policy->view($account, $pilotPlace));
        $this->assertTrue($this->policy->canViewDepartment($account, WaitingList::ATC_DEPARTMENT));
        $this->assertTrue($this->policy->canViewDepartment($account, WaitingList::PILOT_DEPARTMENT));
    }

    #[Test]
    public function view_allows_both_department_permissions_together(): void
    {
        $atcPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forTrainingPosition(TrainingPosition::factory()->create())
            ->create());

        $qualification = Qualification::firstWhere('code', 'PPL')
            ?? Qualification::factory()->create(['code' => 'PPL', 'type' => 'pilot']);

        $pilotPlace = TrainingPlace::withoutEvents(fn () => TrainingPlace::factory()
            ->forQualification($qualification)
            ->create());

        $account = Account::factory()->create();
        $account->givePermissionTo([
            'training-places.view.atc',
            'training-places.view.pilot',
        ]);

        $this->assertTrue($this->policy->viewAny($account));
        $this->assertTrue($this->policy->view($account, $atcPlace));
        $this->assertTrue($this->policy->view($account, $pilotPlace));
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
