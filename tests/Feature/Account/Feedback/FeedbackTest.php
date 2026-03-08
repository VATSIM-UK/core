<?php

namespace Tests\Feature\Account\Feedback;

use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Form;
use App\Models\NetworkData\Atc;
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
    public function test_it_rejects_feedback_submission_without_active_session()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $targetAccount = Account::factory()->create();

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), [
                'account_id' => $targetAccount->id,
                // Assuming there's an account lookup question field
            ])
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('errors');
    }

    #[Test]
    public function test_it_accepts_feedback_submission_with_active_session()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $targetAccount = Account::factory()->create();

        // Create an ATC session within the ±30 minute window
        Atc::factory()->create([
            'account_id' => $targetAccount->id,
            'created_at' => $form->created_at,
            'callsign' => $form->callsign,
        ]);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), [
                'account_id' => $targetAccount->id,
            ])
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success');
    }

    #[Test]
    public function test_it_rejects_feedback_when_session_is_outside_30_minute_window()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $targetAccount = Account::factory()->create();

        // Create an ATC session 35 minutes before the form creation time (outside window)
        Atc::factory()->create([
            'account_id' => $targetAccount->id,
            'created_at' => $form->created_at->subMinutes(35),
            'callsign' => $form->callsign,
        ]);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), [
                'account_id' => $targetAccount->id,
            ])
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('errors');
    }
}
