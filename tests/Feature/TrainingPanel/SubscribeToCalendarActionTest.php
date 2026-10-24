<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel;

use App\Filament\Training\Pages\Dashboard;
use App\Filament\Training\Pages\Exam\UpcomingExams;
use App\Filament\Training\Pages\Mentor\UpcomingMentoringSessions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;

class SubscribeToCalendarActionTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function the_dashboard_links_to_the_calendar_subscription(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(Dashboard::class)
            ->assertActionVisible('subscribeToCalendar')
            ->assertActionHasUrl('subscribeToCalendar', route('site.bookings.calendar.subscribe'));
    }

    #[Test]
    public function upcoming_mentoring_sessions_link_to_the_calendar_subscription(): void
    {
        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class)
            ->assertActionHasUrl('subscribeToCalendar', route('site.bookings.calendar.subscribe'));
    }

    #[Test]
    public function upcoming_exams_link_to_the_calendar_subscription(): void
    {
        $this->panelUser->givePermissionTo('training.exams.view-upcoming.*');

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingExams::class)
            ->assertActionHasUrl('subscribeToCalendar', route('site.bookings.calendar.subscribe'));
    }
}
