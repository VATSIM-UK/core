<?php

namespace Tests\Feature\Mship\Feedback;

use Tests\TestCase;
use Spatie\Permission\Models\Role;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Feedback;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FeedbackAdminTest extends TestCase
{
    use DatabaseTransactions;

    private $form;

    protected function setUp()
    {
        parent::setUp();

        // Load default form
        $this->form = Form::findOrFail(1);
    }

    /** @test */
    public function testAdminCantSeeOwnFeedback()
    {
        // Give user permission to see and view feedback
        $role = factory(Role::class)->create();
        
        $role->givePermissionTo(Permission::create(['name' => 'adm/mship/feedback/view/'.$this->form->slug]));
        $role->givePermissionTo(Permission::findByName('adm/mship/feedback/list/'.$this->form->slug));
        $role->givePermissionTo(Permission::findByName('adm/mship/feedback/list/*'));
        $role->givePermissionTo(Permission::findByName('adm/mship/feedback/view/*'));

        $this->user->assignRole($role->fresh());

        // Create piece of feedback
        $feedback = factory(Feedback::class)->create([
            'account_id' => $this->user->id,
            'form_id' => $this->form->id,
        ]);

        $this->actingAs($this->user->fresh())
            ->get(route('adm.mship.feedback.view', $feedback))
            ->assertRedirect()
            ->assertSessionHasErrors();
    }

    /** @test */
    public function testSuperAdminCanSeeOwnFeedback()
    {
        // Create piece of feedback
        $feedback = factory(Feedback::class)->create([
            'account_id' => $this->privacc->id,
            'form_id' => $this->form->id,
        ]);

        $this->withoutMiddleware('auth_full_group')->actingAs($this->privacc, 'web')
            ->get(route('adm.mship.feedback.view', $feedback))
            ->assertSuccessful();
    }
}
