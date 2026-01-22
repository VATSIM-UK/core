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

    public function test_active_tab_shows_only_non_deleted_feedback()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $activeFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback->markRejected($this->adminUser, 'Invalid');

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertSee($activeFeedback->id)
            ->assertDontSee($rejectedFeedback->id);
    }

    public function test_rejected_tab_shows_only_deleted_feedback()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $activeFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback->markRejected($this->adminUser, 'Invalid');

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->clickTab('Rejected')
            ->assertDontSee($activeFeedback->id)
            ->assertSee($rejectedFeedback->id);
    }

    public function test_active_tab_displays_badge_count()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        factory(Feedback::class, 3)->create(['form_id' => $form->id]);

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->assertSee('3'); // Badge count for 3 active items
    }

    public function test_rejected_tab_displays_badge_count()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        factory(Feedback::class, 2)->create(['form_id' => $form->id])
            ->each(fn ($feedback) => $feedback->markRejected($this->adminUser, 'Invalid'));

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->clickTab('Rejected')
            ->assertSee('2'); // Badge count for 2 rejected items
    }

    public function test_can_view_rejected_feedback_from_list()
    {
        $form = factory(Form::class)->create(['slug' => 'atc']);
        $rejectedFeedback = factory(Feedback::class)->create(['form_id' => $form->id]);
        $rejectedFeedback->markRejected($this->adminUser, 'Invalid feedback');

        $this->adminUser->givePermissionTo('feedback.access');
        $this->adminUser->givePermissionTo("feedback.view-type.{$form->slug}");

        Livewire::actingAs($this->adminUser);
        Livewire::test(ListFeedback::class)
            ->clickTab('Rejected')
            ->assertSee($rejectedFeedback->id);
    }
}
