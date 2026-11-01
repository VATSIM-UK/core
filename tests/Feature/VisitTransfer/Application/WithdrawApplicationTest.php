<?php

namespace Tests\Feature\VisitTransfer\Application;

use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WithdrawApplicationTest extends TestCase
{
    #[Test]
    public function owner_can_withdraw_then_start_again_and_old_urls_return_not_found(): void
    {
        $application = $this->applicationFor($this->user);
        $publicId = $application->public_id;

        $this->actingAs($this->user)
            ->post(route('visiting.application.withdraw.post', [$publicId]))
            ->assertRedirect(route('visiting.landing'))
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('vt_application', ['id' => $application->id]);

        $this->post(route('visiting.application.start.post', [Application::TYPE_VISIT, 'atc']), [
            'application_type' => Application::TYPE_VISIT,
            'training_team' => 'atc',
            'terms_read' => 1,
            'terms_one_hour' => 1,
            'terms_hours_minimum' => 1,
            'terms_hours_minimum_relevant' => 1,
            'terms_recent_transfer' => 1,
            'terms_90_day' => 1,
        ])->assertRedirect();
        $replacement = $this->user->visitTransferApplications()->open()->sole();
        $this->assertNotEquals($application->id, $replacement->id);

        $this->get(route('visiting.application.view', [$publicId]))->assertNotFound();
        $this->get(route('visiting.application.withdraw', [$publicId]))->assertNotFound();
        $this->post(route('visiting.application.withdraw.post', [$publicId]))->assertNotFound();
        $this->assertDatabaseHas('vt_application', ['id' => $replacement->id, 'status' => Application::STATUS_IN_PROGRESS]);
    }

    #[Test]
    public function another_members_application_cannot_be_withdrawn_even_with_an_open_application(): void
    {
        $own = $this->applicationFor($this->user);
        $other = $this->applicationFor(Account::factory()->createQuietly());

        $this->actingAs($this->user)
            ->get(route('visiting.application.withdraw', [$other->public_id]))
            ->assertForbidden();
        $this->post(route('visiting.application.withdraw.post', [$other->public_id]))->assertForbidden();
        $this->assertDatabaseHas('vt_application', ['id' => $own->id, 'status' => Application::STATUS_SUBMITTED]);
        $this->assertDatabaseHas('vt_application', ['id' => $other->id, 'status' => Application::STATUS_SUBMITTED]);
    }

    private function applicationFor(Account $account): Application
    {
        return Application::factory()->createQuietly([
            'account_id' => $account->id,
            'type' => Application::TYPE_VISIT,
            'training_team' => 'atc',
            'status' => Application::STATUS_SUBMITTED,
        ]);
    }
}
