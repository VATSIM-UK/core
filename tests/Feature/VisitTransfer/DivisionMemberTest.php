<?php

namespace Tests\Feature\VisitTransferLegacy;

use App\Models\VisitTransferLegacy\Facility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DivisionMemberTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->user->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');
        $this->user = $this->user->fresh();

        $this->actingAs($this->user);
    }

    public function testItLoadsOk()
    {
        $this->get(route('visiting.landing'))
            ->assertSuccessful();
    }

    public function testItDoesntAllowMemberToStartVisitingAtcApplication()
    {
        factory(Facility::class)->states('atc_visit')->create();

        $this->get(route('visiting.landing'))
            ->assertSeeText(trans('application.dashboard.apply.atc.visit.unable'));
    }

    public function testItDoesntAllowMemberToStartTransferringAtcApplication()
    {
        factory(Facility::class)->states('atc_transfer')->create();

        $this->get(route('visiting.landing'))
            ->assertSeeText(trans('application.dashboard.apply.atc.transfer.unable'));
    }

    public function testItDoesntDisplayReferencesTableIfNotAReferee()
    {
        $this->get(route('visiting.landing'))
            ->assertDontSeeText('Pending References');
    }
}
