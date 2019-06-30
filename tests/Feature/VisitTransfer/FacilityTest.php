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

    private $internationalUser;
    private $divisionUser;

    public function setUp(): void
    {
        parent::setUp();

        // Create international user
        $this->internationalUser = factory(Account::class)->create();
        $this->internationalUser->addState(\App\Models\Mship\State::findByCode('INTERNATIONAL'), 'USA', 'USA-N');
        $this->internationalUser = $this->internationalUser->fresh();

        // Create division user
        $this->divisionUser = $this->user;
        $this->divisionUser->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');
        $this->divisionUser = $this->divisionUser->fresh();
    }

    private function insertFacilities()
    {
        factory(Facility::class, 'atc_visit')->create();
        factory(Facility::class, 'atc_transfer')->create();
        factory(Facility::class, 'pilot_visit')->create();
    }

    public function testNoOptionToApplyWithNoFacilities()
    {
        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    public function testNoOptionToApplyWithNoOpenFacilities()
    {
        factory(Facility::class, 'atc_visit')->create(['open' => false]);

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    public function testOptionToApplyWithHiddenFacilities()
    {
        factory(Facility::class, 'atc_visit')->create(['public' => false]);

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.start'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.no_places'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.no_places'));
    }

    public function testOptionToApplyWithFacilities()
    {
        $this->insertFacilities();

        $this->actingAs($this->internationalUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.start'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.start'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.start'));
    }

    public function testNoOptionToApplyWhenDivisionMember()
    {
        $this->withoutMiddleware(MustHaveCommunityGroup::class);
        $this->insertFacilities();

        $this->actingAs($this->divisionUser)
            ->get(route('visiting.landing'))
            ->assertSee(trans('application.dashboard.apply.atc.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.pilot.visit.unable'))
            ->assertSee(trans('application.dashboard.apply.atc.transfer.unable'));
    }

    public function testNoOptionToApplyWhenHasOpenApplication()
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

    public function testHasOptionToContinueWhenHasOpenApplication()
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
}
