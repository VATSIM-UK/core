<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor\Widgets;

use App\Services\Training\MentorPermissionService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class ManageMentorsStatsWidget extends StatsOverviewWidget
{
    public string $category = '';

    protected ?string $pollingInterval = null;

    protected static ?int $sort = 1;

    /**
     * @var int | string | array<string, int | null>
     */
    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        if ($this->category === '') {
            return [];
        }

        $total = app(MentorPermissionService::class)
            ->accountsWithMentoringInCategoryQuery($this->category)
            ->count();

        return [
            Stat::make('Total mentors', Number::format($total, precision: 0))
                ->description('Members with at least one permission in this training group')
                ->icon('heroicon-o-user-group')
                ->color('primary'),
        ];
    }
}
