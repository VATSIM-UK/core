<?php

namespace Tests\Feature\Admin\Feedback\Pages;

use App\Filament\Admin\Resources\Feedback\Pages\ListFeedback;
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
        factory(Feedback::class)->create(['form_id' => $form->id]);

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

        $this->assertNotNull($rejectedFeedback->fresh()->deleted_at);
        $this->assertNull($activeFeedback->fresh()->deleted_at);
    }

    public function test_rejected_feedback_tab_shows_deleted_records()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback->markRejected($this->adminUser, 'Invalid');

        // Verify the rejection worked correctly
        $this->assertNotNull($rejectedFeedback->fresh()->deleted_at);
        $this->assertEquals($rejectedFeedback->fresh()->deleted_by, $this->adminUser->id);
    }

    public function test_bulk_action_feedback_hidden_without_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertActionHidden('action_feedback')
            ->assertActionHidden('send_feedback');
    }

    public function test_bulk_action_feedback_visible_with_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertActionVisible('action_feedback')
            ->assertActionVisible('send_feedback');
    }

    public function test_bulk_action_feedback_marks_records_as_actioned()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback1 = factory(Feedback::class)->create(['form_id' => $form->id]);
        $feedback2 = factory(Feedback::class)->create(['form_id' => $form->id]);
        $feedback3 = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertActionVisible('action_feedback')
            ->selectTableRecords([$feedback1->id, $feedback2->id])
            ->callAction('action_feedback', data: [
                'comment' => 'Bulk actioned as part of review.',
            ]);

        $this->assertNotNull($feedback1->fresh()->actioned_at);
        $this->assertNotNull($feedback2->fresh()->actioned_at);
        $this->assertEquals('Bulk actioned as part of review.', $feedback1->fresh()->actioned_comment);
        $this->assertEquals($this->adminUser->id, $feedback1->fresh()->actioned_by_id);
        // Third feedback was not selected, should remain un-actioned
        $this->assertNull($feedback3->fresh()->actioned_at);
    }

    public function test_bulk_send_feedback_marks_records_as_sent_and_actioned()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback1 = factory(Feedback::class)->create(['form_id' => $form->id]);
        $feedback2 = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertActionVisible('send_feedback')
            ->selectTableRecords([$feedback1->id])
            ->callAction('send_feedback', data: [
                'comment' => 'Bulk sent as part of review.',
            ]);

        $feedback1 = $feedback1->fresh();

        $this->assertNotNull($feedback1->sent_at);
        $this->assertNotNull($feedback1->actioned_at);
        $this->assertEquals('Bulk sent as part of review.', $feedback1->sent_comment);
        // Verify it also auto-actioned
        $this->assertNotNull($feedback1->actioned_at);

        // Second feedback was not selected
        $this->assertNull($feedback2->fresh()->sent_at);
        $this->assertNull($feedback2->fresh()->actioned_at);
    }
}
