<?php

namespace Tests\Feature\Mship\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Permission;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackAdmTest extends TestCase
{
    use DatabaseTransactions;

    private $account;
    private $form;

    protected function setUp()
    {
        parent::setUp();

        /* @var Account account */
        $this->account = factory(Account::class)->create();

        $this->form = Form::findOrFail(1);
    }

    /** @test * */
    public function testAdminCantSeeOwnFeedback()
    {
        $role = factory(Role::class)->create();

        Permission::whereName('adm/mship/feedback/view/*')->first()->attachRole($role);
        Permission::whereName('adm/mship/feedback/list/*')->first()->attachRole($role);

        $this->account->roles()->attach($role);

        $feedback = factory(Feedback::class)->create([
            'account_id' => $this->account->id,
            'form_id' => $this->form->id,
        ]);

        $this->withoutMiddleware('auth_full_group')->actingAs($this->account->fresh(), 'web')->get(route('adm.mship.feedback.view', $feedback))
            ->assertRedirect(route('adm.mship.feedback.all'))->assertSessionHas('error',
                'You cannot view your own feedback');
    }

    /** @test **/
    public function testSuperAdminCanStillSeeOwnFeedback()
    {
        $this->account->roles()->attach(Role::find(1));

        $feedback = factory(Feedback::class)->create([
            'account_id' => $this->account->id,
            'form_id' => $this->form->id,
        ]);

        $this->withoutMiddleware('auth_full_group')->actingAs($this->account->fresh(), 'web')->get(route('adm.mship.feedback.view', $feedback))
            ->assertSuccessful();
    }
}
