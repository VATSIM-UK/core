<?php

namespace App\Filament\Training\Pages\TrainingPlace\Widgets;

use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Repositories\Cts\SessionRepository;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MentoringSessionStatsWidget extends BaseWidget
{
    public ?TrainingPlace $trainingPlace = null;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $sessionRepository = app(SessionRepository::class);

        $ctsStudentId = $this->trainingPlace->account->member?->id ?? 0;
        $ctsPositions = $this->trainingPlace->trainableCtsPositions();

        return [
            Stat::make('Total Sessions', $sessionRepository->getTotalSessionsForPositions($ctsPositions, $ctsStudentId))
                ->icon('heroicon-o-document-text')
                ->description('Includes Sweatbox sessions')
                ->color('primary'),

            Stat::make('Total Cancelled Sessions', $sessionRepository->getTotalCancelledSessionsForPositions($ctsPositions, $ctsStudentId))
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('Total No Show Sessions', $sessionRepository->getTotalNoShowSessionsForPositions($ctsPositions, $ctsStudentId))
                ->icon('heroicon-o-document-text')
                ->color('danger'),
        ];
    }
}
