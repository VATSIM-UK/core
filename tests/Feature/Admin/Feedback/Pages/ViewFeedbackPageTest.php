<?php

namespace Tests\Feature\Admin\Feedback;

use App\Filament\Admin\Resources\FeedbackResource\Pages\ViewFeedback;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Feature\Admin\BaseAdminTestCase;

class ViewFeedbackPageTest extends BaseAdminTestCase
{
    use DatabaseTransactions;

    public function test_submitter_not_visible_without_relevant_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->getRouteKey()])
            ->assertDontSee('Submitted By')
            ->assertDontSee($feedback->submitter->name);
    }

    public function test_submitter_visible_with_relevant_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.view-submitter');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertSee('Submitted by')
            ->assertSee($feedback->submitter->name);
    }

    public function test_cant_view_own_feedback_without_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id, 'account_id' => $this->adminUser->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertForbidden();
    }

    public function test_can_view_own_feedback_with_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id, 'account_id' => $this->adminUser->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.view-own');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertSuccessful();
    }

    public function test_can_action_feedback_not_already_actioned_with_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionVisible('action_feedback')
            ->callAction('action_feedback', data: [
                'comment' => 'Testing action of feedback.',
            ]);

        $this->assertNotNull($feedback->fresh()->actioned_at);

        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionHidden('action_feedback');
    }

    public function test_cant_action_or_sendfeedback_without_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionHidden('action_feedback')
            ->assertActionHidden('send_feedback');
    }

    public function test_can_send_feedback_with_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionVisible('send_feedback')
            ->callAction('send_feedback', data: [
                'comment' => 'Testing sending of feedback.',
            ]);

        $feedback = $feedback->fresh();

        $this->assertNotNull($feedback->sent_at);
        $this->assertNotNull($feedback->actioned_at);
        $this->assertEquals($feedback->actioned_at, $feedback->sent_at);

        $this->assertNotNull($feedback->actioned_comment);
        $this->assertEquals($feedback->sent_comment, 'Testing sending of feedback.');

        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionHidden('send_feedback')
            ->assertActionHidden('action_feedback');
    }

    public function test_cant_view_feedback_of_slug_not_granted_permission_for()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        // not ATC slug
        $this->adminUser->givePermissionTo('feedback.view-type.pilot');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertForbidden();
    }

    public function test_can_reject_feedback_with_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionVisible('reject_feedback')
            ->callAction('reject_feedback');

        $this->assertNotNull($feedback->fresh()->deleted_at);
    }

    public function test_reject_feedback_tracks_deleted_by_user()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->callAction('reject_feedback');

        $feedback = $feedback->fresh();
        $this->assertNotNull($feedback->deleted_at);
        $this->assertEquals($feedback->deleted_by, $this->adminUser->id);
    }

    public function test_can_reject_feedback_with_reason()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertActionVisible('reject_feedback')
            ->callAction('reject_feedback', data: [
                'reason' => 'This feedback does not meet our standards.',
            ]);

        $feedback = $feedback->fresh();
        $this->assertNotNull($feedback->deleted_at);
        $this->assertEquals($feedback->reject_reason, 'This feedback does not meet our standards.');
    }

    public function test_rejected_feedback_shows_in_details_page()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        // Reject the feedback
        $feedback->markRejected($this->adminUser, 'Invalid feedback');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertSuccessful()
            ->assertSee('Rejected By')
            ->assertSee($this->adminUser->name)
            ->assertSee('Rejection Reason')
            ->assertSee('Invalid feedback');
    }

    public function test_rejected_feedback_hides_action_buttons()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$feedback->form->slug}");
        $this->adminUser->givePermissionTo('feedback.action');

        // Reject the feedback
        $feedback->markRejected($this->adminUser, 'Invalid feedback');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ViewFeedback::class, ['record' => $feedback->id])
            ->assertSuccessful()
            ->assertActionHidden('reject_feedback')
            ->assertActionHidden('send_feedback')
            ->assertActionHidden('action_feedback');
    }
}
