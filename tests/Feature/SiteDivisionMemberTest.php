<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SiteDivisionMemberTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(\App\Models\Mship\Account::class)->create();
        $this->account->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');
        $this->account = $this->account->fresh();
        $this->account->syncWithDefaultCommunityGroup();
        $this->account = $this->account->fresh();

        $this->actingAs($this->account);
    }

    public function testItLoadsOk()
    {
        $response = $this->get(route('visiting.landing'));
        $response->assertSuccessful();
    }

    public function testItDoesntAllowMemberToStartVisitingAtcApplication()
    {
        factory(\App\Models\VisitTransfer\Facility::class, 'atc_visit')->create();

        $response = $this->get(route('visiting.landing'));
        $response->assertSeeText(trans('application.dashboard.apply.atc.visit.unable'));
    }

    public function testItDoesntAllowMemberToStartTransferringAtcApplication()
    {
        factory(\App\Models\VisitTransfer\Facility::class, 'atc_transfer')->create();

        $response = $this->get(route('visiting.landing'));
        $response->assertSeeText(trans('application.dashboard.apply.atc.transfer.unable'));
    }

    public function testItDoesntDisplayReferencesTableIfNotAReferee()
    {
        $response = $this->get(route('visiting.landing'));
        $response->assertDontSeeText('Pending References');
    }
}
