<?php

declare(strict_types=1);

namespace Tests\Feature\TrainingPanel\Statistics;

use App\Filament\Training\Pages\Statistics\AtcTrainingStatistics;
use App\Models\Cts\Position as CtsPosition;
use App\Models\Mship\Account;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\TrainingPanel\BaseTrainingPanelTestCase;

class AtcTrainingStatisticsTest extends BaseTrainingPanelTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_loads_when_user_has_atc_statistics_view_permission(): void
    {
        $this->panelUser->givePermissionTo('training.statistics.view.atc');

        Livewire::actingAs($this->panelUser)
            ->test(AtcTrainingStatistics::class)
            ->assertSuccessful();
    }

    #[Test]
    public function it_is_forbidden_when_user_has_no_statistics_view_permission(): void
    {
        Livewire::actingAs($this->panelUser)
            ->test(AtcTrainingStatistics::class)
            ->assertForbidden();
    }

    #[Test]
    public function it_displays_a_section_for_each_rating_training_atc_training_group(): void
    {
        $this->panelUser->givePermissionTo('training.statistics.view.atc');

        $component = Livewire::actingAs($this->panelUser)
            ->test(AtcTrainingStatistics::class);

        foreach (MentorPermissionService::atcRatingTrainingCategories() as $category) {
            $component->assertSee($category);
        }

        $component->assertDontSee('Heathrow GMC')->assertDontSee('Heathrow AIR')->assertDontSee('Heathrow APC');
    }

    #[Test]
    public function it_displays_the_four_key_statistics_for_training_groups(): void
    {
        $this->panelUser->givePermissionTo('training.statistics.view.atc');

        $category = MentorPermissionService::atcRatingTrainingCategories()[0];
        $trainingPosition = $this->createTrainingPosition($category, 'EGCC_GND');

        TrainingPlace::factory()->create([
            'account_id' => Account::factory()->create()->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        Livewire::actingAs($this->panelUser)
            ->test(AtcTrainingStatistics::class)
            ->assertSee('Active Training Places')
            ->assertSee('Avg. Sessions to Rating')
            ->assertSee('Avg. Training Duration')
            ->assertSee('Exam First Pass Rate');
    }

    private function createTrainingPosition(string $category, string $callsign): TrainingPosition
    {
        CtsPosition::firstOrCreate(['callsign' => $callsign]);

        return TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => [$callsign],
        ]);
    }
}
