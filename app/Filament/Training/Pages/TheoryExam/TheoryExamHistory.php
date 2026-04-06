<?php

namespace App\Filament\Training\Pages\TheoryExam;

use App\Filament\Training\Pages\TheoryExam\Widgets\TheoryExamOverview;
use App\Filament\Training\Support\TheoryExamViewTrait;
use App\Repositories\Cts\TheoryExamResultRepository;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class TheoryExamHistory extends Page implements HasTable
{
    use InteractsWithTable;
    use TheoryExamViewTrait;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.theory-exam-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Theory';

    public static function canAccess(): bool
    {

        return auth()->user()->can('training.theory.access');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TheoryExamOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        $userPermissionsTruthTable = [
            's1' => auth()->user()->can('training.theory.view.obs'),
            's2' => auth()->user()->can('training.theory.view.twr'),
            's3' => auth()->user()->can('training.theory.view.app'),
            'c1' => auth()->user()->can('training.theory.view.ctr'),
        ];

        $typesToShow = collect($userPermissionsTruthTable)->filter(fn ($value) => $value)->keys();

        $theoryExamResultRepository = app(TheoryExamResultRepository::class);
        $query = $theoryExamResultRepository->getTheoryExamHistoryQueryForLevels($typesToShow);

        return $table->query($query)->columns([
            TextColumn::make('student.cid')->label('CID')->searchable(),
            TextColumn::make('student.account.name')->label('Name'),
            TextColumn::make('exam')->label('Exam'),
            TextColumn::make('result')->getStateUsing(fn ($record) => $record->resultHuman())->badge()->color(fn ($state) => match ($state) {
                'Passed' => 'success',
                'Failed' => 'danger',
                default => 'gray',
            })->label('Result'),
            TextColumn::make('submitted_time')->label('Submitted')->isoDateTimeFormat('lll'),
        ])->defaultSort('submitted_time', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->icon(null)
                    ->color('primary')
                    ->modalHeading(fn ($record) => (($record->student?->account?->name) ?? 'Unknown')."'s {$record->exam} Theory Exam")
                    ->schema([
                        ...$this->theoryExamInfoList(),
                    ]),
            ])
            ->filters([
                Filter::make('exam_date')->schema([
                    DatePicker::make('exam_date_from')->label('From'),
                    DatePicker::make('exam_date_to')->label('To'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['exam_date_from'], fn ($query, $date) => $query->whereDate('submitted_time', '>=', $date))
                        ->when($data['exam_date_to'], fn ($query, $date) => $query->whereDate('submitted_time', '<=', $date));
                })->label('Exam date'),
                Filter::make('exam_rating')->schema([
                    Select::make('exam_rating')
                        ->options([
                            'S1' => 'OBS/Student (S1)',
                            'S2' => 'Tower (S2)',
                            'S3' => 'Approach (S3)',
                            'C1' => 'Enroute (C1)',
                        ])
                        ->multiple()
                        ->label('Exam'),
                ])->query(function ($query, array $data) {
                    return $query
                        ->when($data['exam_rating'], fn ($query, $exam_ratings) => $query->where(function ($subQuery) use ($exam_ratings) {
                            foreach ($exam_ratings as $exam_rating) {
                                $subQuery->orWhere('exam', 'LIKE', "%{$exam_rating}%");
                            }
                        })
                        );
                })->label('Exam'),
            ])
            ->paginated(['25', '50', '100'])
            ->defaultPaginationPageOption(25);
    }
}
