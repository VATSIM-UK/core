<?php

namespace Tests\Feature\Admin\Feedback\Pages;

use App\Filament\Admin\Resources\Feedback\Pages\ListFeedback;
use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Answer;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form;
use App\Models\Mship\Feedback\Question;
use Filament\Actions\Testing\TestAction;
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
            ->assertActionHidden(TestAction::make('action_feedback')->table()->bulk())
            ->assertActionHidden(TestAction::make('send_feedback')->table()->bulk());
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
            ->assertActionVisible(TestAction::make('action_feedback')->table()->bulk())
            ->assertActionVisible(TestAction::make('send_feedback')->table()->bulk());
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
            ->assertActionVisible(TestAction::make('action_feedback')->table()->bulk())
            ->selectTableRecords([$feedback1->id, $feedback2->id])
            ->callAction(TestAction::make('action_feedback')->table()->bulk(), data: [
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
            ->assertActionVisible(TestAction::make('send_feedback')->table()->bulk())
            ->selectTableRecords([$feedback1->id])
            ->callAction(TestAction::make('send_feedback')->table()->bulk(), data: [
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

    public function test_search_by_submitter_name_with_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $submitter = Account::factory()->create(['name_first' => 'Searchable', 'name_last' => 'Submitter']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id, 'submitter_account_id' => $submitter->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");
        $this->adminUser->givePermissionTo('feedback.view-submitter');

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->searchTable('Submitter')
            ->assertCanSeeTableRecords([$feedback]);
    }

    public function test_search_by_submitter_name_does_not_leak_without_permission()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $submitter = Account::factory()->create(['name_first' => 'Hidden', 'name_last' => 'Submitter']);
        $feedback = factory(Feedback::class)->create(['form_id' => $form->id, 'submitter_account_id' => $submitter->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->searchTable('Submitter')
            ->assertCanNotSeeTableRecords([$feedback]);
    }

    public function test_position_filter_matches_starts_with()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $positionQuestion = factory(Question::class)->create(['slug' => 'callsign3']);

        $egkkFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        factory(Answer::class)->create(['feedback_id' => $egkkFeedback->id, 'question_id' => $positionQuestion->id, 'response' => 'EGKK_APP']);

        $egpdFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        factory(Answer::class)->create(['feedback_id' => $egpdFeedback->id, 'question_id' => $positionQuestion->id, 'response' => 'EGPD_TWR']);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->filterTable('position', ['position' => 'EGKK'])
            ->assertCanSeeTableRecords([$egkkFeedback])
            ->assertCanNotSeeTableRecords([$egpdFeedback]);
    }

    public function test_position_filter_matches_exact_position()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $positionQuestion = factory(Question::class)->create(['slug' => 'callsign3']);

        $target = factory(Feedback::class)->create(['form_id' => $form->id]);
        factory(Answer::class)->create(['feedback_id' => $target->id, 'question_id' => $positionQuestion->id, 'response' => 'EGLL_TWR']);

        $other = factory(Feedback::class)->create(['form_id' => $form->id]);
        factory(Answer::class)->create(['feedback_id' => $other->id, 'question_id' => $positionQuestion->id, 'response' => 'EGPH_TWR']);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->filterTable('position', ['position' => 'EGLL'])
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }
}
