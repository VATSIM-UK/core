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

class MyAcceptedMentoringSessionsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        return $table
            ->heading('My Accepted Mentoring Sessions')
            ->description('Your mentoring sessions that have been accepted')
            ->query(
                Session::query()
                    ->with(['student', 'mentor'])
                    ->where('student_id', auth()->user()->member->id)
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime')
                    ->where('taken', 1)
                    ->where('noShow', 0)
            )
            ->defaultSort('taken_date', 'asc')
            ->columns([
                TextColumn::make('mentor_name')
                    ->label('Mentor')
                    ->getStateUsing(fn (Session $record) => $record->mentor?->name ?? 'Unknown')
                    ->description(fn (Session $record) => $record->mentor?->cid ?? 'Unknown'),

                TextColumn::make('position')
                    ->label('Position')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('taken_date')
                    ->label('Date & Time')
                    ->getStateUsing(function (Session $record) {
                        $date = Carbon::parse($record->taken_date)->format('d/m/Y');
                        $start = Carbon::parse($record->taken_from)->format('H:i');
                        $end = Carbon::parse($record->taken_to)->format('H:i');

                        return trim("{$date} {$start}Z - {$end}Z");
                    })
                    ->description(function (Session $record) {
                        $sessionStart = Carbon::parse("{$record->taken_date} {$record->taken_from}");

                        if ($sessionStart->isPast()) {
                            return 'Started '.$sessionStart->diffForHumans();
                        }

                        return 'Starts '.$sessionStart->diffForHumans();
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
        return view('livewire.training.my-accepted-mentoring-sessions-table');
    }
}
