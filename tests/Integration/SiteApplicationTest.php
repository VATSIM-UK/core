<?php

namespace Tests\Integration;

use Tests\BrowserKitTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteApplicationTest extends BrowserKitTestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(\App\Models\Mship\Account::class)->create();
        $this->account->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');

        $this->actingAs($this->account);
    }

    /** @test */
//    public function itDoesntAllowMemberToStartVisitAtcApplication()
//    {
//        factory(App\Models\VisitTransfer\Facility::class, 'atc_visit')->create();
//
//        $this->visit(route('visiting.landing'))
//            ->see(trans('application.dashboard.apply.atc.visit.unable'));
//    }

    /** @test */
//    public function itDoesntAllowMemberToStartTransferringAtcApplication()
//    {
//        factory(App\Models\VisitTransfer\Facility::class, 'atc_visit')->create();
//
//        $this->visit(route('visiting.landing'))
//            ->see(trans('application.dashboard.apply.atc.transfer.unable'));
//    }

    /** @test */
    public function itDoesntDisplayReferencesTableIfNotAReferee()
    {
        $this->visit(route('visiting.landing'))
            ->dontSee('Pending References');
    }
}
