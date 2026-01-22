<?php

namespace App\Filament\Training\Pages\Exam;

use App\Filament\Training\Pages\Exam\Widgets\ExamOverview;
use App\Repositories\Cts\ExamResultRepository;
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
            TextColumn::make('student.account.id')->label('CID')->searchable(),
            TextColumn::make('student.account.name')->label('Name'),
            TextColumn::make('examBooking.exam')->label('Exam'),
            TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman($record->result))->badge()->color(fn ($state) => match ($state) {
                'Passed' => 'success',
                'Failed' => 'danger',
                'Incomplete' => 'warning',
                default => 'gray',
            })->label('Result'),
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
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['exam_date_from'], fn ($query, $date) => $query->whereHas('examBooking', function ($q) use ($date) {
                            $q->whereDate('taken_date', '>=', $date);
                        }))
                        ->when($data['exam_date_to'], fn ($query, $date) => $query->whereHas('examBooking', function ($q) use ($date) {
                            $q->whereDate('taken_date', '<=', $date);
                        }));
                })->label('Exam date'),
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
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['position'], fn ($query, $positions) => $query->whereHas('examBooking', function ($q) use ($positions) {
                            $q->where(function ($subQuery) use ($positions) {
                                foreach ($positions as $position) {
                                    $subQuery->orWhere('position_1', 'LIKE', "%{$position}%");
                                }
                            });
                        }));
                })->label('Position'),
            ]);
    }
}
