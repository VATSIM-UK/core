<?php

namespace Tests\Feature\SmartCars;

use App\Models\Mship\Account;
use App\Models\Smartcars\Flight;
use App\Models\Smartcars\Pirep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartcarsWebInterfaceTest extends TestCase
{
    use RefreshDatabase;

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
    public function itRedirectsFromDashboardAsGuest()
    {
        $this->get(route('fte.dashboard'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheDashboard()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.dashboard'))
                ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromMapAsGuest()
    {
        $this->get(route('fte.map'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheMap()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.map'))
                ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromGuideAsGuest()
    {
        $this->get(route('fte.guide'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheGuide()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.guide'))
                ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromExerciseIndexAsGuest()
    {
        $this->get(route('fte.exercises'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheExerciseIndex()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.exercises'))
                ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromExerciseAsGuest()
    {
        $this->get(route('fte.exercises', $this->exercise))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheExercise()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.exercises', $this->exercise))
                ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromHistoryAsGuest()
    {
        $this->get(route('fte.history'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsHistory()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.history'))
                ->assertSuccessful();
    }

    /** @test * */
    public function itRedirectsFromPirepAsGuest()
    {
        $this->get(route('fte.history', $this->pirep->id))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsPirep()
    {
        $this->actingAs($this->pirep->bid->account, 'web')->get(route('fte.history', $this->pirep->id))
                ->assertSuccessful();
    }

    /** @test * */
    public function itDoesntLoadPirepForWrongUser()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.history', $this->pirep->id))
                ->assertForbidden();
    }
}
