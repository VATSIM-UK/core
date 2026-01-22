<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use DatabaseTransactions;

    private $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = Form::find(1);
    }

    #[Test]
    public function test_it_redirects_from_feedback_form_selector_as_guest()
    {
        $this->get(route('mship.feedback.new'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_the_feedback_form_selector()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new'))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_redirects_from_feedback_form_as_guest()
    {
        $this->get(route('mship.feedback.new.form', $this->form->slug))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_it_loads_the_feedback_form()
    {
        $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new.form', $this->form->slug))
            ->assertSuccessful();
    }

    #[Test]
    public function test_it_fills_user_cid_in_atc_form()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $request = $this->actingAs($this->user, 'web')
            ->call('GET', route('mship.feedback.new.form', $form->slug), ['cid' => 'mycidishere']);

        $request->assertSuccessful();
        $request->assertSee('mycidishere');
    }

    public function test_it_redirects_to_atc_feedback()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $request = $this->actingAs($this->user, 'web')
            ->call('GET', route('mship.feedback.redirect.atc'), ['cid' => 'mycidishere']);

        $request->assertRedirect(route('mship.feedback.new.form', [$form->slug, 'cid' => 'mycidishere']));
    }

    #[Test]
    public function it_reallocates_feedback_to_another_account()
    {
        $originalAccount = Account::factory()->create();
        $newAccount = Account::factory()->create();

        $form = Form::first();

        $feedback = new Feedback([
            'form_id' => $form->id,
            'account_id' => $originalAccount->id,
            'submitter_account_id' => $originalAccount->id,
        ]);
        $feedback->save();

        $this->assertEquals($originalAccount->id, $feedback->account_id);

        $feedback->reallocate($newAccount->id);
        $feedback->refresh();

        $this->assertEquals($newAccount->id, $feedback->account_id);
    }

    //    #[Test]
    //    public function testItAllowsSubmission()
    //    {
    //        //
    //    }
    //
    //    #[Test]
    //    public function testItAllowsCreationOfFormWithPermission()
    //    {
    //        //
    //    }
    //
    //    #[Test]
    //    public function testItAllowsViewingOfSubmissionWithPermission()
    //    {
    //        //
    //    }
    //
    //    #[Test]
    //    public function testItDoesNotAllowViewingOfSubmissionWithoutPermission()
    //    {
    //        //
    //    }
}
