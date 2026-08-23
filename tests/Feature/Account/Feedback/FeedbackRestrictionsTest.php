<?php

namespace Tests\Feature\Account\Feedback;

use App\Enums\Feedback\FormRestrictionSubject;
use App\Enums\Feedback\FormRestrictionType;
use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\FormRestriction;
use App\Models\Mship\Qualification;
use App\Models\NetworkData\Atc;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackRestrictionsTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_form_eligible_with_no_restrictions()
    {
        $form = Form::whereSlug('atc')->first();
        $account = Account::factory()->create();

        $this->assertTrue($form->isEligibleFor($account));
    }

    #[Test]
    public function test_form_eligible_when_qualification_restriction_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $this->assertTrue($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_form_ineligible_when_qualification_restriction_not_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 5, // C1 rating
        ]);

        $account = Account::factory()->create();
        $qualification = Qualification::ofType('atc')->networkValue(2)->first();
        $account->qualifications()->attach($qualification->id);

        $this->assertFalse($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_form_eligible_when_hours_restriction_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 10, // 10 hours
        ]);

        $account = Account::factory()->create();

        // Create ATC sessions totaling more than 10 hours (600 minutes)
        Atc::create([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(12),
            'disconnected_at' => now()->subHours(1),
            'minutes_online' => 660,
        ]);

        $this->assertTrue($form->isEligibleFor($account));
    }

    #[Test]
    public function test_form_ineligible_when_hours_restriction_not_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100, // 100 hours
        ]);

        $account = Account::factory()->create();

        // Create ATC sessions totaling only 5 hours
        Atc::create([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(6),
            'disconnected_at' => now()->subHours(1),
            'minutes_online' => 300,
        ]);

        $this->assertFalse($form->isEligibleFor($account));
    }

    #[Test]
    public function test_unmet_restrictions_returns_only_failed_restrictions()
    {
        $form = Form::whereSlug('atc')->first();

        // Add a satisfied restriction
        $qualification = Qualification::ofType('atc')->networkValue(2)->first();
        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        // Add an unsatisfied restriction
        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $unmet = $form->unmetRestrictionsFor($account->fresh());

        $this->assertCount(1, $unmet);
        $this->assertEquals(FormRestrictionType::Hours, $unmet->first()->type);
    }

    #[Test]
    public function test_form_selector_shows_eligible_forms()
    {
        $form = Form::whereSlug('atc')->first();

        $response = $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new'));

        $response->assertSuccessful();
        $response->assertSee($form->name);
    }

    #[Test]
    public function test_form_selector_shows_ineligible_forms_with_reasons()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $response = $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new'));

        $response->assertSuccessful();
        $response->assertSee('unavailable');
        $response->assertSee('100');
    }

    #[Test]
    public function test_ineligible_user_redirected_from_form_view()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $this->actingAs($this->user, 'web')
            ->get(route('mship.feedback.new.form', $form->slug))
            ->assertRedirect(route('mship.feedback.new'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function test_ineligible_user_redirected_from_submission()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $this->actingAs($this->user, 'web')
            ->post(route('mship.feedback.new.form.post', $form->slug), [])
            ->assertRedirect(route('mship.feedback.new'))
            ->assertSessionHas('error');
    }

    #[Test]
    public function test_qualification_reason_message()
    {
        $form = Form::whereSlug('atc')->first();

        $restriction = FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2, // S1
        ]);

        $reason = $restriction->reason();

        $this->assertIsString($reason);
        $this->assertNotEmpty($reason);
        $this->assertStringContainsString('S1', $reason);
    }

    #[Test]
    public function test_hours_reason_message()
    {
        $form = Form::whereSlug('atc')->first();

        $restriction = FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $reason = $restriction->reason();

        $this->assertIsString($reason);
        $this->assertStringContainsString('100', $reason);
        $this->assertStringContainsString('hours', $reason);
    }

    #[Test]
    public function test_multiple_restrictions_all_must_be_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        // Qualification restriction (satisfied)
        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        // Hours restriction (not satisfied)
        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $this->assertFalse($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_form_restriction_belongs_to_form()
    {
        $form = Form::whereSlug('atc')->first();

        $restriction = FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 10,
        ]);

        $this->assertInstanceOf(Form::class, $restriction->form);
        $this->assertEquals($form->id, $restriction->form->id);
    }

    #[Test]
    public function test_account_with_no_atc_qualification_fails_qualification_restriction()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        $account = Account::factory()->create();
        // No qualification attached

        $this->assertFalse($form->isEligibleFor($account));
    }

    #[Test]
    public function test_account_with_no_atc_hours_fails_hours_restriction()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 10,
        ]);

        $account = Account::factory()->create();
        // No ATC sessions

        $this->assertFalse($form->isEligibleFor($account));
    }
}
