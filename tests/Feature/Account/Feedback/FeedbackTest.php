<?php

namespace Tests\Feature\Account\Feedback;

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
    public function test_it_stores_the_atc_qualification_id_on_submission()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $qualification = \App\Models\Mship\Qualification::factory()->create(['type' => 'atc']);

        $account = \App\Models\Mship\Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $formData = $this->buildFormData($form, $account);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), $formData)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mship_feedback', [
            'account_id' => $account->id,
            'account_atc_qualification_id' => $qualification?->id,
        ]);
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
