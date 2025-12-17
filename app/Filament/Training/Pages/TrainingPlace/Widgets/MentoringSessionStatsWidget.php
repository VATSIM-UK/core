<?php

namespace App\Filament\Training\Pages\TrainingPlace\Widgets;

use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Repositories\Cts\SessionRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MentoringSessionStatsWidget extends BaseWidget
{
    public ?TrainingPlace $trainingPlace = null;

    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $sessionRepository = app(SessionRepository::class);

        return [
            Stat::make('Total Sessions', $sessionRepository->getTotalSessionsForPositions($this->trainingPlace->trainingPosition->cts_positions, $this->trainingPlace->waitingListAccount->member->id))
                ->icon('heroicon-o-document-text')
                ->description('Includes Sweatbox sessions')
                ->color('primary'),

            Stat::make('Total No Show Sessions', $sessionRepository->getTotalNoShowSessionsForPositions($this->trainingPlace->trainingPosition->cts_positions))
                ->icon('heroicon-o-document-text')
                ->color('danger'),
        ];
    }
}
