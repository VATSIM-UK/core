<?php

declare(strict_types=1);

namespace Tests\Unit\Training;

use App\Filament\Training\Support\MentoringTrainingGroupBadgeColor;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Services\Training\MentorPermissionService;
use Filament\Support\Colors\Color;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MentoringTrainingGroupBadgeColorTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns_distinct_colors_for_known_categories(): void
    {
        $this->assertSame(Color::Sky, MentoringTrainingGroupBadgeColor::forCategory('OBS to S1 Training'));
        $this->assertSame(Color::Blue, MentoringTrainingGroupBadgeColor::forCategory('S2 Training'));
        $this->assertSame(Color::Emerald, MentoringTrainingGroupBadgeColor::forCategory('S3 Training'));
        $this->assertSame(Color::Cyan, MentoringTrainingGroupBadgeColor::forCategory('P1 Training'));
    }

    #[Test]
    public function it_returns_gray_for_unknown_categories(): void
    {
        $this->assertSame('gray', MentoringTrainingGroupBadgeColor::forCategory('Unknown Category'));
        $this->assertSame('gray', MentoringTrainingGroupBadgeColor::forCategory(null));
    }

    #[Test]
    public function it_resolves_badge_color_from_cts_callsign(): void
    {
        $category = MentorPermissionService::atcCategories()[0];

        TrainingPosition::factory()->create([
            'category' => $category,
            'cts_positions' => ['EGLL_GND'],
        ]);

        $this->assertSame(
            MentoringTrainingGroupBadgeColor::forCategory($category),
            MentoringTrainingGroupBadgeColor::forCtsCallsign('EGLL_GND')
        );
    }
}
