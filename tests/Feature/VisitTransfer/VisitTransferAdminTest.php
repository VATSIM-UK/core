<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Reference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VisitTransferAdminTest extends TestCase
{
    use RefreshDatabase;

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
        $this->user->roles()->attach(Role::find(1));
    }

    /** @test * */
    public function itShowsBothReferences()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertSee('Reference 1 - '.$this->ref1->account->real_name)
            ->assertSee('Reference 2 - '.$this->ref2->account->real_name);
    }

    /** @test * */
    public function itDoesntShowDeletedReferences()
    {
        $this->ref1->delete();
        $this->actingAs($this->user, 'web')
            ->get(route('adm.visiting.application.view', $this->application->id))
            ->assertDontSee('Reference 1 - '.$this->ref1->account->real_name)
            ->assertSee('Reference 1 - '.$this->ref2->account->real_name)
            ->assertSee('Application has system deleted references in addition to the below:');
    }
}
