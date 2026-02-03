<?php

namespace Tests\Feature\VisitTransfer\Facility;

use App\Filament\Admin\Resources\VisitTransfer\FacilityResource\Pages\CreateFacility;
use App\Filament\Admin\Resources\VisitTransfer\FacilityResource\Pages\EditFacility;
use App\Filament\Admin\Resources\VisitTransfer\FacilityResource\Pages\ListFacilities;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTestCase;
use App\Enums\QualificationTypeEnum;
use App\Models\Mship\Qualification;

class FacilityTest extends BaseAdminTestCase
{
    protected array $applications = [];

    private $internationalUser;

    private $divisionUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->facility = Facility::factory()->create();

        // Create international user
        $this->internationalUser = Account::factory()->create();
        $this->internationalUser->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'), 'USA', 'USA-N');
        $this->internationalUser = $this->internationalUser->fresh();

        // Create division user
        $this->divisionUser = $this->user;
        $this->divisionUser->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');
        $this->divisionUser = $this->divisionUser->fresh();

    }

    private function insertFacilities()
    {
        Facility::factory()->visit('atc')->create();
        Facility::factory()->transfer('atc')->create();
        Facility::factory()->visit('pilot')->create();
    }

    #[Test]
    public function it_loads_if_user_has_basic_access()
    {
        $this->adminUser->givePermissionTo('vt.facility.view.*');

        Livewire::actingAs($this->adminUser)
            ->test(ListFacilities::class)
            ->assertSuccessful()
            ->assertSee($this->facility->name)
            ->assertSee($this->facility->training_team)
            ->assertSee($this->facility->open ? 'Yes' : 'No');
    }

    #[Test]
    public function it_does_not_load_if_user_lacks_basic_access()
    {
        Livewire::actingAs($this->adminUser)
            ->test(ListFacilities::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_allows_creating_if_user_has_create_access()
    {
        $this->adminUser->givePermissionTo(['vt.facility.view.*', 'vt.facility.create']);

        Livewire::actingAs($this->adminUser)
            ->test(CreateFacility::class)
            ->assertSuccessful()
            ->fillForm([
                'name' => 'New Facility',
                'training_team' => 'atc',
                'open' => true,
                'public' => true,
                'can_visit' => true,
                'can_transfer' => false,
                'training_required' => false,
                'training_spaces' => null,
                'stage_statement_enabled' => true,
                'stage_checks' => true,
                'auto_acceptance' => false,
                'description' => 'This is a new facility for testing purposes.',
                'acceptance_emails' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vt_facility', [
            'name' => 'New Facility',
            'training_team' => 'atc',
            'open' => true,
            'description' => 'This is a new facility for testing purposes.',
        ]);
    }

    #[Test]
    public function it_prevents_creating_if_user_does_not_have_create_access()
    {
        $this->adminUser->givePermissionTo(['vt.facility.view.*']);

        Livewire::actingAs($this->adminUser)
            ->test(CreateFacility::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_allows_updating_if_user_has_update_access()
    {
        $this->adminUser->givePermissionTo(['vt.facility.view.*', 'vt.facility.update.*']);

        Livewire::actingAs($this->adminUser)
            ->test(EditFacility::class, ['record' => $this->facility->id])
            ->assertSuccessful()
            ->fillForm([
                'name' => 'Updated Facility',
                'training_team' => 'atc',
                'open' => false,
                'public' => true,
                'can_visit' => true,
                'can_transfer' => false,
                'training_required' => false,
                'training_spaces' => null,
                'stage_statement_enabled' => true,
                'stage_checks' => true,
                'auto_acceptance' => false,
                'description' => 'This is a new facility for testing purposes.',
                'acceptance_emails' => [],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('vt_facility', [
            'name' => 'Updated Facility',
            'training_team' => 'atc',
            'open' => false,
            'description' => 'This is a new facility for testing purposes.',
        ]);
    }

    #[Test]
    public function it_prevents_updating_if_user_does_not_have_update_access()
    {
        $this->adminUser->givePermissionTo(['vt.facility.view.*']);

        Livewire::actingAs($this->adminUser)
            ->test(EditFacility::class, ['record' => $this->facility->id])
            ->assertForbidden();
    }

    // public tests

    #[Test]
    public function test_no_option_to_apply_with_no_facilities()
    {
        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    #[Test]
    public function test_no_option_to_apply_with_no_open_facilities()
    {
        Facility::factory()->visit('atc')->create(['open' => false]);

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    #[Test]
    public function test_option_to_apply_with_hidden_facilities()
    {
        Facility::factory()->visit('atc')->create(['public' => false]);

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.start'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    #[Test]
    public function test_option_to_apply_with_facilities()
    {
        $this->insertFacilities();

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.start'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.start'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.start'));
    }

    #[Test]
    public function test_no_option_to_apply_when_division_member()
    {
        $this->insertFacilities();

        $this->actingAs($this->divisionUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.unable'));
    }

    #[Test]
    public function test_no_option_to_apply_when_has_open_application()
    {
        $this->insertFacilities();
        $this->internationalUser->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
        ]);

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.visit_open'));
    }

    #[Test]
    public function test_has_option_to_continue_when_has_open_application()
    {
        $this->insertFacilities();
        $this->internationalUser->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
            'training_team' => 'pilot',

        ]);

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.continue'))
            ->assertSee(trans('application.dashboard.apply.visit_open'));
    }

    #[Test]
    public function it_correctly_identifies_if_a_user_is_qualified_for_atc_facility()
    {
        $minQual = Qualification::ofType(QualificationTypeEnum::ATC->value)->where('vatsim', 3)->first();
        $maxQual = Qualification::ofType(QualificationTypeEnum::ATC->value)->where('vatsim', 4)->first();
        
        $facility = Facility::factory()->create([
            'training_team' => 'atc',
            'minimum_atc_qualification_id' => $minQual->id,
            'maximum_atc_qualification_id' => $maxQual->id,
        ]);

        $application = new Application(['account_id' => $this->internationalUser->id]);

        // 1. User is S1, Should fail (Too low)
        $s1 = Qualification::ofType(QualificationTypeEnum::ATC->value)->where('vatsim', 2)->first();
        $this->internationalUser->addQualification($s1);
        $this->assertFalse($application->isQualifiedFor($facility));

        // 2. User is S2, Should pass
        $this->internationalUser->addQualification($minQual);
        $this->internationalUser->refresh();
        $this->assertTrue($application->isQualifiedFor($facility->fresh()));

        // 3. User is C1, Should fail (Too high)
        $c1 = Qualification::ofType(QualificationTypeEnum::ATC->value)->where('vatsim', 5)->first();
        $this->internationalUser->addQualification($c1);
        $this->internationalUser->refresh();
        $this->assertFalse($application->isQualifiedFor($facility->fresh()));
    }

    #[Test]
    public function it_correctly_identifies_if_a_user_is_qualified_for_pilot_facility()
    {
        $p2 = Qualification::ofType(QualificationTypeEnum::Pilot->value)->where('vatsim', 2)->first();
        
        $facility = Facility::factory()->create([
            'training_team' => 'pilot',
            'minimum_pilot_qualification_id' => $p2->id,
        ]);

        $application = new Application(['account_id' => $this->internationalUser->id]);

        // User has no pilot qualifications, Should fail
        $this->assertFalse($application->isQualifiedFor($facility));

        // User gains P2, Should pass
        $this->internationalUser->addQualification($p2);
        $this->internationalUser->refresh();
        $this->assertTrue($application->isQualifiedFor($facility->fresh()));
    }
}
