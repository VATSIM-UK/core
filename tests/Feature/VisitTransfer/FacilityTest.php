<?php

namespace Tests\Feature\VisitTransfer;

use App\Http\Middleware\MustHaveCommunityGroup;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FacilityTest extends TestCase
{
    use DatabaseTransactions;

    private $intlAccount;
    private $divisionAccount;

    public function setUp()
    {
        parent::setUp();
        $this->intlAccount = factory(Account::class)->create();
        $this->intlAccount->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'), 'USA', 'USA-N');
        $this->intlAccount = $this->intlAccount->fresh();

        $this->divisionAccount = factory(Account::class)->create();
        $this->divisionAccount->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');
        $this->divisionAccount = $this->divisionAccount->fresh();
    }

    private function insertFacilities()
    {
        factory(Facility::class, 'atc_visit')->create();
        factory(Facility::class, 'atc_transfer')->create();
        factory(Facility::class, 'pilot_visit')->create();
    }

    public function testNoOptionToApplyWithNoFacilities()
    {
        $this->actingAs($this->intlAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    public function testNoOptionToApplyWithNoOpenFacilities()
    {
        factory(Facility::class, 'atc_visit')->create(['open' => false]);
        $this->actingAs($this->intlAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    public function testOptionToApplyWithHiddenFacilities()
    {
        factory(Facility::class, 'atc_visit')->create(['public' => false]);
        $this->actingAs($this->intlAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.start'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    public function testOptionToApplyWithFacilities()
    {
        $this->insertFacilities();

        $this->actingAs($this->intlAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.start'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.start'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.start'));
    }

    public function testNoOptionToApplyWhenDivisionMember()
    {
        $this->withoutMiddleware(MustHaveCommunityGroup::class);
        $this->insertFacilities();
        $this->actingAs($this->divisionAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.unable'));
    }

    public function testNoOptionToApplyWhenHasOpenApplication()
    {
        $this->insertFacilities();
        $this->intlAccount->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
        ]);
        $this->actingAs($this->intlAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.visit_open'));
    }

    public function testHasOptionToContinueWhenHasOpenApplication()
    {
        $this->insertFacilities();
        $this->intlAccount->createVisitingTransferApplication([
            'type' => Application::TYPE_VISIT,
            'training_team' => 'pilot',

        ]);
        $this->actingAs($this->intlAccount)->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.continue'))
            ->assertSee(trans('application.dashboard.apply.visit_open'));
    }
}
