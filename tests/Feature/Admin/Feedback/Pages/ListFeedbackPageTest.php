<?php

namespace Tests\Feature\Admin\Feedback\Pages;

use App\Filament\Admin\Resources\FeedbackResource\Pages\ListFeedback;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ListFeedbackPageTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    public function test_active_tab_is_default()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $activeFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertSuccessful();
    }

    public function test_active_tab_filters_deleted_feedback()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $activeFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback->markRejected($this->adminUser, 'Invalid');

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertTableRecordNotExists($rejectedFeedback->id)
            ->assertTableRecordExists($activeFeedback->id);
    }

    public function test_rejected_tab_filters_to_deleted_feedback_only()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $activeFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback->markRejected($this->adminUser, 'Invalid');

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->set('activeTab', 'Rejected')
            ->assertTableRecordExists($rejectedFeedback->id)
            ->assertTableRecordNotExists($activeFeedback->id);
    }
}
