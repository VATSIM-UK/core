<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Exam\ConductExam;
use App\Models\Cts\ExamBooking;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class AcceptedExamsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected $listeners = ['exam-accepted' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Accepted Exams')
            ->description('Exams that are currently accepted and you are assigned to conduct will be displayed here.')
            ->query(ExamBooking::query()
                ->with(['student', 'examiners'])
                ->conductable()
                ->whereHas('examiners', function ($query) {
                    $accountMemberId = auth()->user()->member->id;

                    return $query->where('senior', $accountMemberId)
                        ->orWhere('other', $accountMemberId)
                        ->orWhere('trainee', $accountMemberId);
                }))
            ->columns([
                TextColumn::make('student.cid')->label('CID'),
                TextColumn::make('student.name')->label('Name'),
                TextColumn::make('examiners.primaryExaminer.name')->label('Primary Examiner'),
                TextColumn::make('exam')->label('Level'),
                TextColumn::make('position_1')->label('Position'),
                TextColumn::make('start_date')->label('Date'),
            ])
            ->actions([
                Action::make('Conduct')
                    ->url(fn (ExamBooking $exam): string => ConductExam::getUrl(['examId' => $exam->id]))
                    ->visible(fn (ExamBooking $examBooking) => $examBooking->finished != ExamBooking::FINISHED_FLAG),
            ]);
    }

    public function render()
    {
        return view('livewire.training.accepted-exams-table');
    }
}
