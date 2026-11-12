<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplicationStartRouteTest extends TestCase
{
    #[Test]
    public function starting_an_application_requires_a_state_changing_request()
    {
        $this->actingAs(Account::factory()->create())
            ->get(route('visiting.application.start', [Application::TYPE_VISIT, 'pilot']))
            ->assertRedirect(route('landing'));

        $this->assertDatabaseCount('vt_application', 0);
    }

    #[Test]
    public function a_member_can_start_a_pilot_application()
    {
        $this->actingAs(Account::factory()->create())
            ->post(route('visiting.application.start', [Application::TYPE_VISIT, 'pilot']))
            ->assertRedirect(route('visiting.application.facility', [Application::first()->public_id]));

        $this->assertDatabaseCount('vt_application', 1);
    }

    #[Test]
    public function an_atc_application_is_created_from_the_terms_page()
    {
        $this->actingAs(Account::factory()->create())
            ->post(route('visiting.application.start', [Application::TYPE_VISIT, 'atc']))
            ->assertSuccessful();

        $this->post(route('visiting.application.start.post', [Application::TYPE_VISIT, 'atc']), [
            'terms_read' => 1,
            'terms_one_hour' => 1,
            'terms_hours_minimum' => 1,
            'terms_hours_minimum_relevant' => 1,
            'terms_recent_transfer' => 1,
            'terms_90_day' => 1,
            'application_type' => Application::TYPE_VISIT,
            'training_team' => 'atc',
        ])->assertRedirect(route('visiting.application.facility', [Application::first()->public_id]));

        $this->assertDatabaseCount('vt_application', 1);
    }
}
