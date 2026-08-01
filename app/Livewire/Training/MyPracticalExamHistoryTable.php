<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Exam\ViewExamReport;
use App\Models\Cts\PracticalResult;
use App\Services\Training\ExamHistoryService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class MyPracticalExamHistoryTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $examHistoryService = app(ExamHistoryService::class);
        $user = auth()->user();

        return $table
            ->heading('Practical Exam History')
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

                TextColumn::make('examBooking.start_date')->label('Exam date')->formatStateUsing(fn ($state) => Carbon::parse($state)->isoFormat('lll')),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                Action::make('view')->label('View Report')->url(fn ($record) => ViewExamReport::getUrl(['examId' => $record->examid])),
            ]);
    }

    public function render()
    {
        return view('livewire.training.my-practical-exam-history-table');
    }
}
