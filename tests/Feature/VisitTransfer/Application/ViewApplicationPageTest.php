<?php

namespace Tests\Feature\VisitTransfer\Application;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource\Pages\ViewVisitTransferApplication;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Facility;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewApplicationPageTest extends BaseAdminTestCase
{
    protected array $applications = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser->givePermissionTo('vt.application.view.*');

        // by deafult it uses team = atc
        $facility = Facility::factory()->create();
        $this->application = Application::factory()->transfer()->create();

    }

    #[Test]
    public function it_loads_if_user_has_basic_access()
    {
        Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful();
    }

    #[Test]
    public function it_does_not_load_if_user_lacks_basic_access()
    {
        Livewire::actingAs(Account::factory()->create())
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertForbidden();
    }

    #[Test]
    public function it_displays_act_application_details()
    {
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful();

        $component->assertSee($this->application->public_id);
        $component->assertSee($this->application->account->full_name);
        $component->assertSee($this->application->type_string);
        $component->assertSee($this->application->facility->name);
        $component->assertSee($this->application->status_string);
    }

    #[Test]
    public function it_displays_visiting_application_details()
    {
        $application = Application::factory()->visit()->create();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $application->id])
            ->assertSuccessful();

        $component->assertSee($application->public_id);
        $component->assertSee($application->account->full_name);
        $component->assertSee($application->type_string);
        $component->assertSee($application->facility->name);
        $component->assertSee($application->status_string);
    }

    #[Test]
    public function it_shows_accept_action_if_user_can_accept()
    {
        $this->adminUser->givePermissionTo('vt.application.accept.*');

        $this->application->check_outcome_90_day = true;
        $this->application->check_outcome_50_hours = true;
        $this->application->save();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionVisible('accept');

    }

    #[Test]
    public function it_hides_accept_action_if_user_cannot_accept_due_permission()
    {
        $this->application->check_outcome_90_day = true;
        $this->application->check_outcome_50_hours = true;
        $this->application->save();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('accept');

    }

    #[Test]
    public function it_hides_accept_action_if_user_cannot_accept_due_hour_checks()
    {
        $this->adminUser->givePermissionTo('vt.application.accept.*');

        $this->application->check_outcome_90_day = false;
        $this->application->check_outcome_50_hours = true;
        $this->application->save();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('accept');

    }

    #[Test]
    public function it_hides_accept_action_if_user_cannot_accept_due_status_check()
    {
        $this->adminUser->givePermissionTo('vt.application.accept.*');

        $this->application->check_outcome_90_day = true;
        $this->application->check_outcome_50_hours = true;
        $this->application->status = Application::STATUS_IN_PROGRESS;
        $this->application->save();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('accept');

    }

    #[Test]
    public function it_shows_reject_action_if_user_can_reject()
    {
        $this->adminUser->givePermissionTo('vt.application.reject.*');

        $this->application->check_outcome_90_day = false;
        $this->application->check_outcome_50_hours = false;
        $this->application->save();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionVisible('reject');
    }

    #[Test]
    public function it_hides_reject_action_if_user_cannot_reject_due_permission()
    {
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('reject');
    }

    #[Test]
    public function it_hides_reject_action_if_user_cannot_reject_due_status()
    {
        $this->adminUser->givePermissionTo('vt.application.reject.*');
        $this->application->status = Application::STATUS_WITHDRAWN;
        $this->application->save();

        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('reject');
    }

    #[Test]
    public function it_shows_complete_action_if_user_can_complete()
    {
        $this->adminUser->givePermissionTo('vt.application.complete.*');
        $this->application->status = Application::STATUS_ACCEPTED;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionVisible('complete');
    }

    #[Test]
    public function it_hides_complete_action_if_user_cannot_complete_due_permission()
    {
        $this->application->status = Application::STATUS_ACCEPTED;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('complete');
    }

    #[Test]
    public function it_hides_complete_action_if_user_cannot_complete_due_status()
    {
        $this->adminUser->givePermissionTo('vt.application.complete.*');
        $this->application->status = Application::STATUS_SUBMITTED;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('complete');
    }

    #[Test]
    public function it_shows_cancel_action_if_user_can_cancel()
    {
        $this->adminUser->givePermissionTo('vt.application.cancel.*');
        $this->application->status = Application::STATUS_ACCEPTED;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionVisible('cancel');
    }

    #[Test]
    public function it_hides_cancel_action_if_user_cannot_cancel_due_permission()
    {
        $this->application->status = Application::STATUS_ACCEPTED;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('cancel');
    }

    #[Test]
    public function it_hides_cancel_action_if_user_cannot_cancel_due_status()
    {
        $this->adminUser->givePermissionTo('vt.application.cancel.*');
        $this->application->status = Application::STATUS_SUBMITTED;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('cancel');
    }

    #[Test]
    public function it_shows_override_checks_action_if_user_can_override_checks()
    {
        $this->adminUser->givePermissionTo('vt.application.accept.*');
        $this->application->check_outcome_90_day = false;
        $this->application->check_outcome_50_hours = false;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionVisible('override_checks');
    }

    #[Test]
    public function it_hides_override_checks_action_if_user_cannot_override_checks_due_permission()
    {
        $this->application->check_outcome_90_day = false;
        $this->application->check_outcome_50_hours = false;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('override_checks');
    }

    #[Test]
    public function it_hides_override_checks_action_if_user_cannot_override_checks_due_checks()
    {
        $this->adminUser->givePermissionTo('vt.application.accept.*');
        $this->application->check_outcome_90_day = true;
        $this->application->check_outcome_50_hours = true;
        $this->application->save();
        $component = Livewire::actingAs($this->adminUser)
            ->test(ViewVisitTransferApplication::class, ['record' => $this->application->id])
            ->assertSuccessful()
            ->assertActionHidden('override_checks');
    }
}
