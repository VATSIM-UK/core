<?php

namespace App\Filament\Training\Pages\Exam;

use App\Filament\Training\Pages\Exam\Widgets\ExamOverview;
use App\Services\Training\ExamHistoryService;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class ExamHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.training.pages.exam-history';

    protected static ?string $navigationGroup = 'Exams';

    public static function canAccess(): bool
    {

        return auth()->user()->can('training.exams.access');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ExamOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        $examHistoryService = app(ExamHistoryService::class);
        $user = auth()->user();

        return $table->query($examHistoryService->getExamHistoryQuery($user))->columns([
            TextColumn::make('student.account.id')->label('CID')->searchable(),
            TextColumn::make('student.account.name')->label('Name'),
            TextColumn::make('examBooking.exam')->label('Exam'),
            TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => $examHistoryService->getResultBadgeColor($state))->label('Result'),
            TextColumn::make('examBooking.position_1')->label('Position'),
            TextColumn::make('examBooking.start_date')->label('Exam date'),
            TextColumn::make('date')->label('Report filed'),
        ])->defaultSort('date', 'desc')
            ->actions([
                Action::make('view')->label('View')->url(fn ($record) => ViewExamReport::getUrl(['examId' => $record->examid])),
            ])
            ->filters([
                Filter::make('exam_date')->form([
                    Forms\Components\DatePicker::make('exam_date_from')->label('From'),
                    Forms\Components\DatePicker::make('exam_date_to')->label('To'),
                ])->query(fn ($query, array $data) => $examHistoryService->applyExamDateFilter($query, $data))->label('Exam date'),
                Filter::make('position')->form([
                    Forms\Components\Select::make('position')
                        ->options([
                            'OBS' => 'Observer',
                            'TWR' => 'Tower',
                            'APP' => 'Approach',
                            'CTR' => 'Enroute',
                        ])
                        ->multiple()
                        ->label('Position'),
                ])->query(fn ($query, array $data) => $examHistoryService->applyPositionFilter($query, $data))->label('Position'),
                Filter::make('conducted_by_me')->form([
                    Forms\Components\Checkbox::make('conducted_by_me')
                        ->label('Show exams I conducted'),
                ])->query(fn ($query, array $data) => $examHistoryService->applyConductedByMeFilter($query, $data, $user->id))->label('Conducted by me'),
            ]);
    }
}
