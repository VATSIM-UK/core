<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Statistics;

use App\Services\Training\MentorPermissionService;
use Filament\Pages\Page;

class AtcTrainingStatistics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.training.pages.atc-training-statistics';

    protected static ?int $navigationSort = 10;

    protected static string|\UnitEnum|null $navigationGroup = 'Statistics';

    protected static ?string $title = 'ATC Training Stats';

    protected ?string $subheading = 'Ongoing ATC training statistics for each Training Group.';

    protected static ?string $slug = 'statistics/atc-training-groups';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('training.statistics.view.atc') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('training.statistics.view.atc') ?? false;
    }

    /**
     * @return array<int, string>
     */
    public function getCategories(): array
    {
        return MentorPermissionService::atcRatingTrainingCategories();
    }
}
