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

    #[Test]
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

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $eventTime = now()->subMinutes(10);

        // Create an ATC session within the +-30 minute window (around event time)
        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'frequency' => 118.500,
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => $eventTime,
            'disconnected_at' => $eventTime->copy()->addMinutes(10),
        ]);
        $session->timestamps = false;
        $session->save();

        $formData = $this->buildFormData($form, $account, $eventTime);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), $formData)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mship_feedback', [
            'account_id' => $account->id,
            'account_atc_qualification_id' => $qualification?->id,
        ]);
    }

    #[Test]
    public function test_it_rejects_feedback_submission_without_active_session()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $targetAccount = Account::factory()->create();
        $eventTime = now()->subMinutes(10);

        $formData = $this->buildFormData($form, $targetAccount, $eventTime);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), $formData)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function test_it_accepts_feedback_submission_with_active_session()
    {
        $form = Form::whereSlug('atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find atc form');
        }

        $targetAccount = Account::factory()->create();
        $eventTime = now()->subMinutes(10);

        // Create an active ATC session (no disconnected_at) that started before the event time
        $session = new Atc([
            'account_id' => $targetAccount->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'frequency' => 118.500,
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => $eventTime,
            'disconnected_at' => null,
        ]);
        $session->timestamps = false;
        $session->save();

        $formData = $this->buildFormData($form, $targetAccount, $eventTime);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), $formData)
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
        $eventTime = now();

        // Create an ATC session 35 minutes before event time (outside window)
        $sessionTime = $eventTime->copy()->subMinutes(35);
        $session = new Atc([
            'account_id' => $targetAccount->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'frequency' => 118.500,
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => $sessionTime,
            'disconnected_at' => $sessionTime->copy()->addMinutes(5),
        ]);
        $session->timestamps = false;
        $session->save();

        $formData = $this->buildFormData($form, $targetAccount, $eventTime);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), $formData)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function test_it_accepts_non_atc_feedback_without_active_session()
    {
        $form = Form::where('slug', '!=', 'atc')->first();
        if (! $form) {
            $this->markTestSkipped('could not find non-ATC form');
        }

        $targetAccount = Account::factory()->create();
        $eventTime = now();

        $formData = $this->buildFormData($form, $targetAccount, $eventTime);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), $formData)
            ->assertRedirect(route('mship.manage.dashboard'))
            ->assertSessionHas('success');
    }

    /**
     * Build form data with answers to all questions.
     */
    private function buildFormData(Form $form, Account $targetAccount, $eventTime = null): array
    {
        $formData = [];
        $eventTime = $eventTime ?? now();

        foreach ($form->questions as $question) {
            if ($question->type->name == 'userlookup') {
                $formData[$question->slug] = $targetAccount->id;
            } elseif ($question->type->name == 'datetime') {
                $formData[$question->slug] = $eventTime->format('Y-m-d H:i');
            } elseif ($question->type->requires_value) {
                if (isset($question->options['values']) && ! empty($question->options['values'])) {
                    $formData[$question->slug] = $question->options['values'][0];
                }
            } else {
                $formData[$question->slug] = 'Test answer for '.$question->slug;
            }
        }

        return $formData;
    }
}
