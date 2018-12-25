<?php

namespace Tests\Feature\SmartCars;

use Tests\TestCase;
use App\Models\Mship\Account;
use App\Models\Smartcars\Pirep;
use App\Models\Smartcars\Flight;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SmartcarsWebInterfaceTest extends TestCase
{
    use DatabaseTransactions;

    private $account;
    private $exercise;
    private $pirep;

    public function setUp()
    {
        parent::setUp();
        $this->account = factory(Account::class)->create();
        $this->exercise = factory(Flight::class)->create();
        $this->pirep = factory(Pirep::class)->create();
    }

    /** @test * */
    public function testItRedirectsFromDashboardAsGuest()
    {
        $this->get(route('fte.dashboard'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testItLoadsTheDashboardAndExerciseButton()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.dashboard'))
                ->assertSuccessful()
                ->assertSeeText('View All Exercises');
    }

    /** @test * */
    public function testItRedirectsWhenNoExercisesAvailable()
    {
        $this->exercise->enabled = false;
        $this->pirep->bid->flight->enabled = false;

        $this->exercise->save();
        $this->pirep->bid->flight->save();

        $this->actingAs($this->account, 'web')->get(route('fte.exercises'))
            ->assertRedirect(route('fte.dashboard'))
            ->assertSessionHas('error', 'There are no exercises available at the moment.');
    }

    /** @test * */
    public function testItRedirectsFromGuideAsGuest()
    {
        $this->get(route('fte.guide'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testItLoadsTheGuide()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.guide'))
                ->assertSuccessful();
    }

    /** @test * */
    public function testItRedirectsFromExerciseIndexAsGuest()
    {
        $this->get(route('fte.exercises'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testItLoadsTheExerciseIndex()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.exercises'))
                ->assertSuccessful();
    }

    /** @test * */
    public function testItRedirectsFromExerciseAsGuest()
    {
        $this->get(route('fte.exercises', $this->exercise))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testItLoadsTheExercise()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.exercises', $this->exercise))
                ->assertSuccessful();
    }

    /** @test * */
    public function testItRedirectsFromHistoryAsGuest()
    {
        $this->get(route('fte.history'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testItLoadsHistory()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.history'))
                ->assertSuccessful();
    }

    /** @test * */
    public function testItRedirectsFromPirepAsGuest()
    {
        $this->get(route('fte.history', $this->pirep->id))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testItLoadsPirep()
    {
        $this->actingAs($this->pirep->bid->account, 'web')->get(route('fte.history', $this->pirep->id))
                ->assertSuccessful();
    }

    /** @test * */
    public function testItDoesntLoadPirepForWrongUser()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.history', $this->pirep->id))
                ->assertForbidden();
    }
}
