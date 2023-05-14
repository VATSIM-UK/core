<?php

namespace Tests\Feature\FTE;

use App\Models\Smartcars\Flight;
use App\Models\Smartcars\Pirep;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FTEWebInterfaceTest extends TestCase
{
    use DatabaseTransactions;

    private $exercise;

    private $pirep;

    public function setUp(): void
    {
        parent::setUp();
        $this->exercise = factory(Flight::class)->create();
        $this->pirep = factory(Pirep::class)->create();
    }

    /** @test */
    public function testItRedirectsFromDashboardAsGuest()
    {
        $this->get(route('fte.dashboard'))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsTheDashboardAndExerciseButton()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.dashboard'))
            ->assertSuccessful()
            ->assertSeeText('View All Exercises');
    }

    /** @test */
    public function testItRedirectsWhenNoExercisesAvailable()
    {
        $this->exercise->enabled = false;
        $this->pirep->bid->flight->enabled = false;

        $this->exercise->save();
        $this->pirep->bid->flight->save();

        $this->actingAs($this->user, 'web')
            ->get(route('fte.exercises'))
            ->assertRedirect(route('fte.dashboard'))
            ->assertSessionHas('error', 'There are no exercises available at the moment.');
    }

    /** @test */
    public function testItRedirectsFromGuideAsGuest()
    {
        $this->get(route('fte.guide'))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsTheGuide()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.guide'))
            ->assertSuccessful();
    }

    /** @test */
    public function testItRedirectsFromExerciseIndexAsGuest()
    {
        $this->get(route('fte.exercises'))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsTheExerciseIndex()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.exercises'))
            ->assertSuccessful();
    }

    /** @test */
    public function testItRedirectsFromExerciseAsGuest()
    {
        $this->get(route('fte.exercises', $this->exercise))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsTheExercise()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.exercises', $this->exercise))
            ->assertSuccessful();
    }

    /** @test */
    public function testItRedirectsFromHistoryAsGuest()
    {
        $this->get(route('fte.history'))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsHistory()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.history'))
            ->assertSuccessful();
    }

    /** @test */
    public function testItRedirectsFromPirepAsGuest()
    {
        $this->get(route('fte.history', $this->pirep->id))
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testItLoadsPirep()
    {
        $this->actingAs($this->pirep->bid->account, 'web')
            ->get(route('fte.history', $this->pirep->id))
            ->assertSuccessful();
    }

    /** @test */
    public function testItDoesntLoadPirepForWrongUser()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.history', $this->pirep->id))
            ->assertForbidden();
    }
}
