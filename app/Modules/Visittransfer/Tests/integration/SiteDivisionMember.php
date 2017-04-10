<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SiteDivisionMember extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(\App\Models\Mship\Account::class)->create();

        $this->account->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');

        $this->account->fresh();

//        $this->actingAs($this->account);
    }

    /** @test */
    public function it_loads_ok()
    {
        $this->visit(route('visiting.landing'));

        $this->assertResponseOk();
    }

    /** @test */
    public function it_doesnt_allow_member_to_start_visit_atc_application()
    {
        factory(App\Modules\Visittransfer\Models\Facility::class, 'atc_visit')->create();

        $this->visit(route('visiting.landing'))
            ->see(trans('visittransfer::application.dashboard.atc.visit.unable'));
    }

    /** @test */
    public function it_doesnt_allow_member_to_start_transferring_atc_application()
    {
        factory(App\Modules\Visittransfer\Models\Facility::class, 'atc_visit')->create();

        $this->visit(route('visiting.landing'))
            ->see(trans('visittransfer::application.dashboard.apply.atc.transfer.unable'));
    }

    /** @test */
    public function it_doesnt_display_references_table_if_not_a_referee()
    {
        $this->visit(route('visiting.landing'))
            ->dontSee('Pending References');
    }
}
