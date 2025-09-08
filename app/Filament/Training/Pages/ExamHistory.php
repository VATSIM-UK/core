<?php

namespace App\Filament\Training\Pages;

use App\Repositories\Cts\ExamResultRepository;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ExamHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.exam-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.exams.access');
    }

    public function table(Table $table): Table
    {
        $userPermissionsTruthTable = [
            'obs' => auth()->user()->can('training.exams.conduct.obs'),
            'twr' => auth()->user()->can('training.exams.conduct.twr'),
            'app' => auth()->user()->can('training.exams.conduct.app'),
            'ctr' => auth()->user()->can('training.exams.conduct.ctr'),
        ];

        $typesToShow = collect($userPermissionsTruthTable)->filter(fn ($value) => $value)->keys();

        $examResultRepository = app(ExamResultRepository::class);
        $query = $examResultRepository->getExamHistoryQueryForLevels($typesToShow);

        return $table->query($query)->columns([
            TextColumn::make('student.account.id')->label('CID'),
            TextColumn::make('student.account.name')->label('Name'),
            TextColumn::make('examBooking.exam')->label('Exam'),
            TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                'Passed' => 'success',
                'Failed' => 'danger',
                'Incomplete' => 'warning',
                default => 'gray',
            })->label('Result'),
            TextColumn::make('examBooking.position_1')->label('Position'),
            TextColumn::make('examBooking.start_date')->label('Exam date'),
            TextColumn::make('date')->label('Report filed'),
        ])->defaultSort('date', 'desc')
            ->recordActions([
                Action::make('view')->label('View')->url(fn ($record) => ViewExamReport::getUrl(['examId' => $record->examid])),
            ]);
    }
}
