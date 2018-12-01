<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Models\VisitTransfer\Reference;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisitTransferAdminTest extends TestCase
{
    use DatabaseTransactions;

    public $application;
    public $ref1;
    public $ref2;
    public $user;

    public function setUp()
    {
        parent::setUp();
        Mail::fake();

        $this->application = factory(Application::class)->create();
        $this->ref1 = factory(Reference::class)->create(['application_id' => $this->application->id]);
        $this->ref2 = factory(Reference::class)->create(['application_id' => $this->application->id]);
        $this->application = $this->application->fresh();
    }

    /** @test * */
    public function testThatItDisplaysCheckOutcomes()
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
            ->assertSeeTextInOrder(['90 Day Check', 'in excess of 90
                                            days', '50 Hour Check', 'does not have in excess of 50
                                            hours']);

        $this->application->setCheckOutcome('90_day', false);
        $this->application->setCheckOutcome('50_hours', true);
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSeeTextInOrder(['90 Day Check', 'within 90 days', '50 Hour Check', 'in excess of 50 hours']);
    }

    /** @test * */
    public function testThatItShowsBothReferences()
    {
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSee('Reference 1 - '.e($this->ref1->account->real_name))
            ->assertSee('Reference 2 - '.e($this->ref2->account->real_name));
    }

    /** @test * */
    public function testThatItDoesntShowDeletedReferences()
    {
        $this->ref1->delete();
        $this->actingAs($this->privacc, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertDontSee('Reference 1 - '.e($this->ref1->account->real_name))
            ->assertSee('Reference 1 - '.e($this->ref2->account->real_name))
            ->assertSee('Application has system deleted references in addition to the below:');
    }

    /** @test **/
    public function testInfinitePlacesCanBeSelectedForAFacility()
    {
        $this->actingAs($this->privacc, 'web')
            ->post(route('adm.visiting.facility.create.post'), $this->createTestPostData())
            ->assertRedirect(route('adm.visiting.facility'))
            ->assertSessionHas('success');
    }

    /** @test **/
    public function testTrainingSpacesHasToBePresent()
    {
        $array = $this->createTestPostData();

        array_pull($array, 'training_spaces');

        $this->actingAs($this->privacc, 'web')
            ->post(route('adm.visiting.facility.create.post'), $array)
            ->assertRedirect()->assertSessionHas('errors');
    }

    /** @test **/
    public function testNumberOfPlacesCanBeSelectedForAFacility()
    {
        $this->actingAs($this->privacc, 'web')
            ->post(route('adm.visiting.facility.create.post'), array_replace($this->createTestPostData(), ['training_spaces' => 0]))
            ->assertRedirect(route('adm.visiting.facility'));
    }

    private function createTestPostData()
    {
        $basicData = factory(Facility::class)->make()->toArray();

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
