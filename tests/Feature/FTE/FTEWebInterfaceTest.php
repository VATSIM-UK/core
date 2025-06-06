<?php

namespace Tests\Feature\FTE;

use App\Models\Smartcars\Flight;
use App\Models\Smartcars\Pirep;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FTEWebInterfaceTest extends TestCase
{
    use DatabaseTransactions;

    private $exercise;

    private $pirep;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exercise = factory(Flight::class)->create();
        $this->pirep = factory(Pirep::class)->create();
    }

    #[Test]
    public function test_it_redirects_from_dashboard_as_guest()
    {
        $this->get(route('fte.dashboard'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_the_dashboard_and_exercise_button()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.dashboard'))
            ->assertSuccessful()
            ->assertSeeText('View All Exercises');
    }

    #[Test]
    public function test_it_redirects_when_no_exercises_available()
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

    #[Test]
    public function test_it_redirects_from_guide_as_guest()
    {
        $this->get(route('fte.guide'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_the_guide()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.guide'))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_redirects_from_exercise_index_as_guest()
    {
        $this->get(route('fte.exercises'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_the_exercise_index()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.exercises'))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_redirects_from_exercise_as_guest()
    {
        $this->get(route('fte.exercises', $this->exercise))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_the_exercise()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.exercises', $this->exercise))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_redirects_from_history_as_guest()
    {
        $this->get(route('fte.history'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_history()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.history'))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_redirects_from_pirep_as_guest()
    {
        $this->get(route('fte.history', $this->pirep->id))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_pirep()
    {
        $this->actingAs($this->pirep->bid->account, 'web')
            ->get(route('fte.history', $this->pirep->id))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_doesnt_load_pirep_for_wrong_user()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('fte.history', $this->pirep->id))
            ->assertForbidden();
    }
}
