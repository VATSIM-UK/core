<?php

namespace App\Livewire\Training;

use App\Enums\PilotExamType;
use App\Models\Cts\CancelReason;
use App\Services\Training\ExamHistoryService;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ExamCancellationsTable extends Component implements HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $examHistoryService = app(ExamHistoryService::class);
        $user = auth()->user();

        $allowedTypes = $examHistoryService->getTypesToShow($user);

        return $table
            ->heading('Exam Cancellations')
            ->query(
                CancelReason::query()
                    ->where('sesh_type', 'EX')
                    ->whereHas('examBooking', function (Builder $query) use ($allowedTypes) {
                        $query->whereIn('exam', $allowedTypes);
                    })
                    ->with(['examBooking', 'examBooking.student'])
            )
            ->columns([
                TextColumn::make('examBooking.student.cid')
                    ->label('Student CID')
                    ->searchable(),

                TextColumn::make('examBooking.student.account.name')
                    ->label('Student Name')
                    ->searchable(),

                TextColumn::make('examBooking.exam')
                    ->label('Exam'),

                TextColumn::make('reason')
                    ->label('Reason')
                    ->wrap()
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),

                TextColumn::make('date')
                    ->label('Cancelled Date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('reason_by')
                    ->label('Cancelled By CID')
                    ->searchable(),
            ])
            ->filters([
                Filter::make('position')->schema([
                    Select::make('atc_positions')
                        ->options([
                            'OBS' => 'Observer',
                            'TWR' => 'Tower',
                            'APP' => 'Approach',
                            'CTR' => 'Enroute',
                        ])
                        ->multiple()
                        ->label('ATC position'),
                    Select::make('pilot_positions')
                        ->options(collect(PilotExamType::cases())
                            ->mapWithKeys(fn ($type) => [$type->label() => $type->label()])
                            ->toArray()
                        )
                        ->multiple()
                        ->label('Pilot rating'),
                ])->query(fn (Builder $query, array $data) => $examHistoryService->applyPositionFilter($query, $data))
                    ->label('Position'),
            ])
            ->defaultSort('date', 'desc')
            ->emptyStateHeading('No cancelled exams found');
    }

    public function render()
    {
        return view('livewire.training.exam-cancellations-table');
    }
}
