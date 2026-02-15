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

        $ctsStudentId = $this->trainingPlace->waitingListAccount->account->member->id;

        return [
            Stat::make('Total Sessions', $sessionRepository->getTotalSessionsForPositions($this->trainingPlace->trainingPosition->cts_positions, $ctsStudentId))
                ->icon('heroicon-o-document-text')
                ->description('Includes Sweatbox sessions')
                ->color('primary'),

            Stat::make('Total Cancelled Sessions', $sessionRepository->getTotalCancelledSessionsForPositions($this->trainingPlace->trainingPosition->cts_positions, $ctsStudentId))
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('Total No Show Sessions', $sessionRepository->getTotalNoShowSessionsForPositions($this->trainingPlace->trainingPosition->cts_positions, $ctsStudentId))
                ->icon('heroicon-o-document-text')
                ->color('danger'),
        ];
    }
}
