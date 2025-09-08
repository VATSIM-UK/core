<?php

namespace App\Filament\Training\Pages;

use App\Models\Cts\ExamBooking;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class Exams extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.training.pages.exams';

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.exams.access');
    }

    public function table(Table $table): Table
    {
        $table->heading('Accepted Exams');
        $table->description('Exams that are currently accepted and you are assigned to conduct will be
        displayed here.');
        $table->query(ExamBooking::query()
            ->with(['student', 'examiners'])
            ->conductable()
            ->whereHas('examiners', function ($query) {
                $accountMemberId = auth()->user()->member->id;

                return $query->where('senior', $accountMemberId)
                    ->orWhere('other', $accountMemberId)
                    ->orWhere('trainee', $accountMemberId);
            }));

        $table->columns([
            TextColumn::make('student.cid')->label('CID'),
            TextColumn::make('student.name')->label('Name'),
            TextColumn::make('examiners.primaryExaminer.name')->label('Primary Examiner'),
            TextColumn::make('exam')->label('Level'),
            TextColumn::make('position_1')->label('Position'),
            TextColumn::make('start_date')->label('Date'),
        ]);

        $table->recordActions([
            Action::make('Conduct')
                ->url(fn (ExamBooking $exam): string => ConductExam::getUrl(['examId' => $exam->id]))
                ->visible(fn (ExamBooking $examBooking) => $examBooking->finished != ExamBooking::FINISHED_FLAG),
        ]);

        return $table;
    }
}
