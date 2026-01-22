<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Models\VisitTransfer\Reference;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use DatabaseTransactions;

    public $application;

    public $ref1;

    public $ref2;

    public $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->application = Application::factory()->create();
        $this->ref1 = Reference::factory()->create(['application_id' => $this->application->id]);
        $this->ref2 = Reference::factory()->create(['application_id' => $this->application->id]);
        $this->application = $this->application->fresh();
    }

    #[Test]
    public function test_that_it_displays_check_outcomes()
    {
        $this->application->status = Application::STATUS_SUBMITTED;
        $this->application->submitted_at = Carbon::now();
        $this->application->save();

        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSeeTextInOrder(['90 Day Check', 'Data unavailable', '50 Hour Check', 'Data unavailable']);

        $this->application->setCheckOutcome('90_day', true);
        $this->application->setCheckOutcome('50_hours', false);
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSeeTextInOrder(['90 Day Check', 'in excess of 90 days', '50 Hour Check', 'does not have in excess of 50 hours']);

        $this->application->setCheckOutcome('90_day', false);
        $this->application->setCheckOutcome('50_hours', true);
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSeeTextInOrder(['90 Day Check', 'within 90 days', '50 Hour Check', 'in excess of 50 hours']);
    }

    #[Test]
    public function test_that_it_shows_both_references()
    {
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSee('Reference 1 - '.e($this->ref1->account->real_name), false)
            ->assertSee('Reference 2 - '.e($this->ref2->account->real_name), false);
    }

    #[Test]
    public function test_that_it_doesnt_show_deleted_references()
    {
        $this->ref1->delete();
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertDontSee('Reference 1 - '.e($this->ref1->account->real_name), false)
            ->assertSee('Reference 1 - '.e($this->ref2->account->real_name), false)
            ->assertSee('Application has system deleted references in addition to the below:');
    }

    #[Test]
    public function test_infinite_places_can_be_selected_for_a_facility()
    {
        $this->actingAs($this->privacc, 'web')
            ->post(route('adm.visiting.facility.create.post'), $this->createTestPostData())
            ->assertRedirect(route('adm.visiting.facility'))
            ->assertSessionHas('success');
    }

    #[Test]
    public function test_training_spaces_has_to_be_present()
    {
        $array = $this->createTestPostData();

        array_pull($array, 'training_spaces');

        $this->actingAs($this->privacc, 'web')
            ->post(route('adm.visiting.facility.create.post'), $array)
            ->assertRedirect()->assertSessionHas('errors');
    }

    #[Test]
    public function test_number_of_places_can_be_selected_for_a_facility()
    {
        $this->actingAs($this->privacc, 'web')
            ->post(route('adm.visiting.facility.create.post'), array_replace($this->createTestPostData(), ['training_spaces' => 0]))
            ->assertRedirect(route('adm.visiting.facility'));
    }

    private function createTestPostData()
    {
        $basicData = Facility::factory()->make()->toArray();

        $data = [
            'can_visit' => true,
            'can_transfer' => true,
            'training_required' => true,
            'training_team' => 'atc',
            'training_spaces' => null,
            'stage_statement_enabled' => false,
            'stage_reference_enabled' => true,
            'stage_reference_quantity' => 2,
            'stage_checks' => true,
            'auto_acceptance' => true,
            'open' => false,
            'public' => true,
        ];

        return array_merge($basicData, $data);
    }
}
