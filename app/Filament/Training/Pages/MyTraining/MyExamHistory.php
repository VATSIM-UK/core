<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Pages\Exam\ViewExamReport;
use App\Models\Cts\PracticalResult;
use App\Services\Training\ExamHistoryService;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MyExamHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.training.pages.my-training.my-exam-history';

    protected static ?string $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Exam History';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.access') ?? false;
    }

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
