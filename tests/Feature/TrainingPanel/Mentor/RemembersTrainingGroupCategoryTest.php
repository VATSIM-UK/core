<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Mentor;

use App\Filament\Training\Pages\Mentor\ManageMentors;
use App\Filament\Training\Pages\Mentor\MentoringHistory;
use App\Filament\Training\Pages\Mentor\UpcomingMentoringSessions;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class RemembersTrainingGroupCategoryTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    private const SESSION_KEY = 'training.mentoring.last_category';

    private string $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = MentorPermissionService::atcCategories()[0];

        $this->panelUser->givePermissionTo('training.mentors.view.atc');

        CtsPosition::firstOrCreate(['callsign' => 'EGLL_GND']);

        $trainingPosition = TrainingPosition::factory()->create([
            'category' => $this->category,
            'cts_positions' => ['EGLL_GND'],
        ]);

        app(MentorPermissionService::class)->assignToMentorable(
            $this->panelUser,
            $trainingPosition,
            $this->panelUser,
            $this->category,
        );
    }

    #[Test]
    public function it_saves_the_category_to_session_when_visiting_a_mentoring_page(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class, ['category' => $this->category])
            ->assertSet('category', $this->category);

        $this->assertSame($this->category, session(self::SESSION_KEY));
    }

    #[Test]
    public function it_restores_the_category_from_session_when_visiting_without_a_url_param(): void
    {
        session([self::SESSION_KEY => $this->category]);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class)
            ->assertSet('category', $this->category);
    }

    #[Test]
    public function category_persists_across_different_mentoring_screens(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(ManageMentors::class, ['category' => $this->category])
            ->assertSet('category', $this->category);

        Livewire::actingAs($this->panelUser)
            ->test(UpcomingMentoringSessions::class)
            ->assertSet('category', $this->category);

        Livewire::actingAs($this->panelUser)
            ->test(MentoringHistory::class)
            ->assertSet('category', $this->category);
    }
}
