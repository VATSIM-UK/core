<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicationFacilitySelectionTest extends TestCase
{
    #[Test]
    public function the_applicant_can_select_a_facility_with_a_code()
    {
        $facility = Facility::factory()->visit('atc')->create();
        $application = $this->inProgressApplication();

        $this->actingAs($application->account)
            ->post(route('visiting.application.facility.manual.post', $application->public_id), [
                'facility-code' => $facility->public_id,
            ])
            ->assertRedirect(route('visiting.application.continue', $application->public_id))
            ->assertSessionHas('success', 'Facility selection saved!');

        $this->assertSame($facility->id, $application->fresh()->facility_id);
    }

    #[Test]
    public function another_member_cannot_change_the_facility()
    {
        $originalFacility = Facility::factory()->visit('atc')->create();
        $otherFacility = Facility::factory()->visit('atc')->create();

        $victimApplication = Application::factory()->visit('atc')->create([
            'status' => Application::STATUS_SUBMITTED,
            'facility_id' => $originalFacility->id,
        ]);

        $this->actingAs(Account::factory()->create())
            ->post(route('visiting.application.facility.manual.post', $victimApplication->public_id), [
                'facility-code' => $otherFacility->public_id,
            ])
            ->assertForbidden();

        $this->assertSame($originalFacility->id, $victimApplication->fresh()->facility_id);
    }

    #[Test]
    public function the_applicant_cannot_change_the_facility_once_submitted()
    {
        $originalFacility = Facility::factory()->visit('atc')->create();
        $otherFacility = Facility::factory()->visit('atc')->create();

        $application = Application::factory()->visit('atc')->create([
            'account_id' => $this->user->id,
            'status' => Application::STATUS_SUBMITTED,
            'facility_id' => $originalFacility->id,
        ]);

        $this->actingAs($this->user)
            ->post(route('visiting.application.facility.manual.post', $application->public_id), [
                'facility-code' => $otherFacility->public_id,
            ])
            ->assertForbidden();

        $this->assertSame($originalFacility->id, $application->fresh()->facility_id);
    }

    #[Test]
    public function the_applicant_cannot_select_a_facility_their_application_is_not_eligible_for()
    {
        $facility = Facility::factory()->transfer('atc')->create();
        $application = $this->inProgressApplication();

        $this->actingAs($application->account)
            ->from(route('visiting.application.facility', $application->public_id))
            ->post(route('visiting.application.facility.manual.post', $application->public_id), [
                'facility-code' => $facility->public_id,
            ])
            ->assertRedirect(route('visiting.application.facility', $application->public_id))
            ->assertSessionHas('error', 'You are not permitted to apply to this facility.');

        $this->assertNull($application->fresh()->facility_id);
    }

    #[Test]
    public function the_applicant_cannot_select_an_ineligible_facility_from_the_list()
    {
        $facility = Facility::factory()->transfer('atc')->create();
        $application = $this->inProgressApplication();

        $this->actingAs($application->account)
            ->from(route('visiting.application.facility', $application->public_id))
            ->post(route('visiting.application.facility.post', $application->public_id), [
                'facility_id' => $facility->id,
            ])
            ->assertRedirect(route('visiting.application.facility', $application->public_id))
            ->assertSessionHasErrors('permitted');

        $this->assertNull($application->fresh()->facility_id);
    }

    private function inProgressApplication(): Application
    {
        return Application::factory()->visit('atc')->create([
            'account_id' => $this->user->id,
            'status' => Application::STATUS_IN_PROGRESS,
            'facility_id' => null,
        ]);
    }
}
