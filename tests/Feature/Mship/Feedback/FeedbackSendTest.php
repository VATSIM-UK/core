<?php

namespace Tests\Feature\Mship\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Role;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FeedbackSendTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;
    private $member;
    private $feedback;

    public function setUp()
    {
        parent::setUp();

        $this->admin = factory(Account::class)->create();
        $this->admin->roles()->attach(Role::find(1));
        $this->admin->addState(State::findByCode('DIVISION'));

        $this->member = factory(Account::class)->create();
        $this->member->addState(State::findByCode('DIVISION'));

        $this->feedback = factory(Feedback::class)->create([
            'account_id' => $this->member->id,
        ]);
    }

    /** @test * */
    public function itAllowsSendingWithPermission()
    {
        $this->actingAs($this->admin)->post(route('adm.mship.feedback.send', $this->feedback->id))
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    /** @test * */
    public function itDoesNotAllowSendingWithoutPermission()
    {
        $this->actingAs($this->member)->post(route('adm.mship.feedback.send', $this->feedback->id))
            ->assertStatus(403);
    }

    /** @test * */
    public function itOnlyShowsSentFeedbackToMember()
    {
        $unsentForm = $this->feedback;

        $sentForm = factory(Feedback::class)->create([
            'account_id' => $this->member->id,
        ]);
        $sentForm->markSent($this->admin);

        $this->actingAs($this->member)->get(route('mship.feedback.view'))
            ->assertSuccessful();

        // Need to assertVisible and assertNotVisible
    }

    /** @test * */
    public function itRedirectsMemberIfThereIsNoSentFeedback()
    {
        //
    }
}
