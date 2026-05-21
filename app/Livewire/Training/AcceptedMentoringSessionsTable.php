<?php

namespace App\Livewire\Training;

use App\Models\Cts\Session;
use Carbon\Carbon;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class AcceptedMentoringSessionsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected $listeners = ['session-accepted' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Accepted Mentoring Sessions')
            ->description('Mentoring sessions that are currently accepted and you are assigned to conduct will be displayed here.')
            ->query(
                Session::query()
                    ->with(['student', 'mentor'])
                    ->where('mentor_id', auth()->user()->member->id)
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime')
                    ->where('noShow', 0)
            )
            ->defaultSort('taken_date', 'asc')
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->getStateUsing(fn (Session $record) => $record->student->name)
                    ->description(fn (Session $record) => $record->student->cid),

                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('taken_date')
                    ->label('Date & Time')
                    ->getStateUsing(function (Session $record) {
                        $date = Carbon::parse($record->taken_date)->format('d/m/Y');
                        $time = Carbon::parse($record->taken_from)->format('H:i');

                        return trim("{$date} {$time}");
                    })
                    ->sortable(query: fn (Builder $query, string $direction) => $query
                        ->orderBy('taken_date', $direction)
                        ->orderBy('taken_from', $direction)
                    ),
            ])
            ->emptyStateHeading('No upcoming mentoring sessions found');
    }

    public function render()
    {
        return view('livewire.training.accepted-mentoring-sessions-table');
    }
}
