<?php

namespace Tests\Feature\Mship\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedbackSendTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $member;
    private $feedback;

    public function setUp()
    {
        parent::setUp();

        $this->admin = factory(Account::class)->create();
        $this->admin->roles()->attach(Role::find(1));

        $this->member = factory(Account::class)->create();

        $this->feedback = factory(Feedback::class)->create([
            'account_id' => $this->member->id,
        ]);
    }

    /** @test * */
    public function itRedirectsMemberIfThereIsNoSentFeedback()
    {
        $this->actingAs($this->member)->get(route('mship.feedback.view'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }

    /** @test * */
    public function itAllowsViewingIfThereIsSentFeedback()
    {
        $this->feedback->markSent($this->admin);

        $this->actingAs($this->member)->get(route('mship.feedback.view'))
            ->assertSuccessful();
    }

    /** @test * */
    public function itAllowsFeedbackToBeMarkedAsSent()
    {
        $this->actingAs($this->admin)->post(route('adm.mship.feedback.send', $this->feedback->id))
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');
    }
}
