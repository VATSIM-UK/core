<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\VisitTransfer\Facility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DivisionMemberTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->addState(\App\Models\Mship\State::findByCode('DIVISION'), 'EUR', 'GBR');
        $this->user = $this->user->fresh();

        $this->actingAs($this->user);
    }

    public function test_it_loads_ok()
    {
        $this->get(route('visiting.landing'))
            ->assertSuccessful();
    }

    public function test_it_doesnt_allow_member_to_start_visiting_atc_application()
    {
        factory(Facility::class)->states('atc_visit')->create();

        $this->get(route('visiting.landing'))
            ->assertSeeText(trans('application.dashboard.apply.atc.visit.unable'));
    }

    public function test_it_doesnt_allow_member_to_start_transferring_atc_application()
    {
        factory(Facility::class)->states('atc_transfer')->create();

        $this->get(route('visiting.landing'))
            ->assertSeeText(trans('application.dashboard.apply.atc.transfer.unable'));
    }

    public function test_it_doesnt_display_references_table_if_not_a_referee()
    {
        $this->get(route('visiting.landing'))
            ->assertDontSeeText('Pending References');
    }
}
