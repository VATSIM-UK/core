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

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(12),
            'disconnected_at' => now()->subHours(1),
        ]);
        $session->minutes_online = 660;
        $session->timestamps = false;
        $session->save();

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

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(6),
            'disconnected_at' => now()->subHours(1),
        ]);
        $session->minutes_online = 300;
        $session->timestamps = false;
        $session->save();

        $this->assertFalse($form->isEligibleFor($account));
    }

    #[Test]
    public function test_unmet_restrictions_returns_only_failed_restrictions()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();
        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $unmet = $form->unmetRestrictionGroupsFor($account->fresh())
            ->flatten();

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

        $this->assertFalse($form->isEligibleFor($account));
    }

    /*
     * Group (OR) and cross-group (AND) logic
     */

    #[Test]
    public function test_grouped_restrictions_are_eligible_when_only_one_alternative_is_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 5,
        ]);
        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 10,
        ]);

        $account = Account::factory()->create();

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(12),
            'disconnected_at' => now()->subHours(1),
        ]);
        $session->minutes_online = 660;
        $session->timestamps = false;
        $session->save();

        $this->assertTrue($form->isEligibleFor($account));
    }

    #[Test]
    public function test_grouped_restrictions_are_ineligible_when_no_alternative_is_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 5,
        ]);
        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 100,
        ]);

        $account = Account::factory()->create();

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(6),
            'disconnected_at' => now()->subHours(1),
        ]);
        $session->minutes_online = 300;
        $session->timestamps = false;
        $session->save();

        $this->assertFalse($form->isEligibleFor($account));
    }

    #[Test]
    public function test_different_groups_are_and_ed_together()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 2,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 50,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $this->assertFalse($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_different_groups_all_satisfied_makes_form_eligible()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 2,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 10,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(12),
            'disconnected_at' => now()->subHours(1),
        ]);
        $session->minutes_online = 660;
        $session->timestamps = false;
        $session->save();

        $this->assertTrue($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_ungrouped_restriction_is_mandatory_alongside_a_satisfied_group()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);
        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 500,
        ]);

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => null,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 50,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $this->assertFalse($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_two_ungrouped_restrictions_are_each_independently_mandatory()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => null,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);
        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => null,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 50,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $this->assertFalse($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_unmet_restriction_groups_returns_only_the_failed_group()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 2,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 50,
        ]);
        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 2,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 5,
        ]);

        $account = Account::factory()->create();
        $account->qualifications()->attach($qualification->id);

        $unmetGroups = $form->unmetRestrictionGroupsFor($account->fresh());

        $this->assertCount(1, $unmetGroups);
        $this->assertCount(2, $unmetGroups->first());
    }

    #[Test]
    public function test_form_eligible_when_account_age_restriction_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 90,
        ]);

        $account = Account::factory()->create([
            'joined_at' => now()->subDays(200),
        ]);

        $this->assertTrue($form->isEligibleFor($account));
    }

    #[Test]
    public function test_form_ineligible_when_account_age_restriction_not_satisfied()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 90,
        ]);

        $account = Account::factory()->create([
            'joined_at' => now()->subDays(10),
        ]);

        $this->assertFalse($form->isEligibleFor($account));
    }

    #[Test]
    public function test_form_eligible_when_account_age_exactly_meets_minimum()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 90,
        ]);

        $account = Account::factory()->create([
            'joined_at' => now()->subDays(90),
        ]);

        $this->assertTrue($form->isEligibleFor($account));
    }

    #[Test]
    public function test_account_with_no_joined_at_fails_account_age_restriction()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 1,
        ]);

        $account = Account::factory()->create([
            'joined_at' => null,
        ]);

        $this->assertFalse($form->isEligibleFor($account));
    }

    #[Test]
    public function test_account_age_reason_message_describes_days()
    {
        $restriction = FormRestriction::create([
            'form_id' => Form::whereSlug('atc')->first()->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 10,
        ]);

        $reason = $restriction->reason();

        $this->assertIsString($reason);
        $this->assertStringContainsString('10 days', $reason);
    }

    #[Test]
    public function test_account_age_reason_message_describes_months()
    {
        $restriction = FormRestriction::create([
            'form_id' => Form::whereSlug('atc')->first()->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 60,
        ]);

        $reason = $restriction->reason();

        $this->assertStringContainsString('2 months', $reason);
    }

    #[Test]
    public function test_account_age_reason_message_describes_years()
    {
        $restriction = FormRestriction::create([
            'form_id' => Form::whereSlug('atc')->first()->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 365,
        ]);

        $reason = $restriction->reason();

        $this->assertStringContainsString('1 year', $reason);
    }

    #[Test]
    public function test_account_age_and_qualification_restrictions_are_both_mandatory_when_ungrouped()
    {
        $form = Form::whereSlug('atc')->first();

        $qualification = Qualification::ofType('atc')->networkValue(2)->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::Qualification,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 2,
        ]);

        FormRestriction::create([
            'form_id' => $form->id,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 90,
        ]);

        $account = Account::factory()->create([
            'joined_at' => now()->subDays(10),
        ]);
        $account->qualifications()->attach($qualification->id);

        $this->assertFalse($form->isEligibleFor($account->fresh()));
    }

    #[Test]
    public function test_account_age_can_be_one_of_several_or_alternatives_in_a_group()
    {
        $form = Form::whereSlug('atc')->first();

        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::AccountAge,
            'subject' => null,
            'minimum_value' => 365, // not satisfied
        ]);
        FormRestriction::create([
            'form_id' => $form->id,
            'restriction_group' => 1,
            'type' => FormRestrictionType::Hours,
            'subject' => FormRestrictionSubject::Atc,
            'minimum_value' => 10,
        ]);

        $account = Account::factory()->create([
            'joined_at' => now()->subDays(10),
        ]);

        $session = new Atc([
            'account_id' => $account->id,
            'qualification_id' => 1,
            'callsign' => 'EGLL_TWR',
            'facility_type' => Atc::TYPE_TWR,
            'connected_at' => now()->subHours(12),
            'disconnected_at' => now()->subHours(1),
        ]);
        $session->minutes_online = 660;
        $session->timestamps = false;
        $session->save();

        $this->assertTrue($form->isEligibleFor($account));
    }
}
