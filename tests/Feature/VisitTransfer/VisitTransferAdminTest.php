<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use App\Models\VisitTransfer\Reference;
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

        $this->user = factory(Account::class)->create();
        $this->user->assignRole(Role::findById(1));
    }

    /** @test * */
    public function testThatItShowsBothReferences()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSee('Reference 1 - '.$this->ref1->account->real_name)
            ->assertSee('Reference 2 - '.$this->ref2->account->real_name);
    }

    /** @test * */
    public function testThatItDoesntShowDeletedReferences()
    {
        $this->ref1->delete();
        $this->actingAs($this->user, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertDontSee('Reference 1 - '.$this->ref1->account->real_name)
            ->assertSee('Reference 1 - '.$this->ref2->account->real_name)
            ->assertSee('Application has system deleted references in addition to the below:');
    }

    /** @test **/
    public function testInfinitePlacesCanBeSelectedForAFacility()
    {
        $this->actingAs($this->user, 'web')
            ->post(route('adm.visiting.facility.create.post'), $this->createTestPostData())
            ->assertRedirect(route('adm.visiting.facility'))
            ->assertSessionHas('success');
    }

    /** @test **/
    public function testTrainingSpacesHasToBePresent()
    {
        $array = $this->createTestPostData();

        array_pull($array, 'training_spaces');

        $this->actingAs($this->user, 'web')
            ->post(route('adm.visiting.facility.create.post'), $array)
            ->assertRedirect()->assertSessionHas('errors');
    }

    /** @test **/
    public function testNumberOfPlacesCanBeSelectedForAFacility()
    {
        $this->actingAs($this->user, 'web')
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
