<?php

namespace App\Filament\Training\Pages\MyTraining\Widgets;

use App\Filament\Training\Pages\Exam\ViewExamReport;
use App\Models\Cts\PracticalResult;
use App\Services\Training\ExamHistoryService;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MyPracticalExamHistoryTable extends BaseWidget
{
    protected static ?string $heading = 'Practical Exam History';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $examHistoryService = app(ExamHistoryService::class);
        $user = auth()->user();

        return $table
            ->query(
                PracticalResult::query()
                    ->whereHas('student', fn ($q) => $q->where('cid', $user->id))
                    ->with(['student', 'examBooking'])
            )
            ->columns([
                TextColumn::make('examBooking.exam')->label('Exam'),
                TextColumn::make('examBooking.position_1')->label('Position'),
                TextColumn::make('result')
                    ->getStateUsing(fn ($record) => $record->resultHuman())
                    ->badge()
                    ->color(fn ($state) => $examHistoryService->getResultBadgeColor($state))
                    ->label('Result'),

                TextColumn::make('examBooking.start_date')->label('Exam date'),
            ])
            ->defaultSort('date', 'desc')
            ->actions([
                Action::make('view')->label('View Report')->url(fn ($record) => ViewExamReport::getUrl(['examId' => $record->examid])),
            ]);
    }
}
