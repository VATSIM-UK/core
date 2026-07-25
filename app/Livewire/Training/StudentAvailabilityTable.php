<?php

namespace App\Livewire\Training;

use App\Filament\Actions\AcceptMentoringSessionAction;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Livewire\Component;

class StudentAvailabilityTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public TrainingPlace $trainingPlace;

    public function table(Table $table): Table
    {
        $availabilities = $this->getAvailabilities();

        return $table
            ->queryStringIdentifier('availability')
            ->records(fn (): Collection => $availabilities)
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('from')
                    ->label('From')
                    ->time('H:i'),
                TextColumn::make('to')
                    ->label('To')
                    ->time('H:i'),
            ])
            ->defaultSort('date')
            ->paginated(false)
            ->recordActions([
                AcceptMentoringSessionAction::make(
                    name: 'bookSession',
                    label: 'Book Session',
                    color: 'primary',
                    modalHeading: fn (Availability $record): string => "Book Mentoring Session: {$this->trainingPlace->account->name}",
                    modalDescription: fn (Availability $record): string => 'You are booking a mentoring session. Please confirm the exact start and end times below.',
                    modalSubmitActionLabel: 'Book Session',
                    visibilityCondition: fn (): bool => $this->hasPendingSession(),
                    resolveAvailability: fn (array $arguments, $record = null): Availability => $record,
                    resolvePosition: function (Availability $availability, Member $student, ?Session $pendingSession): ?string {
                        return $this->trainingPlace->trainingPosition?->cts_primary_position
                            ?? $this->trainingPlace->trainingPosition?->position?->callsign;
                    },
                    onSuccess: function () {
                        $this->dispatch('session-booked');
                    },
                ),
            ])
            ->emptyStateHeading('No upcoming availability')
            ->emptyStateDescription('The student has not entered any upcoming availability slots.');
    }

    public function hasPendingSession(): bool
    {
        $member = $this->trainingPlace->account->member;

        if (! $member) {
            return false;
        }

        return Session::query()
            ->where('student_id', $member->id)
            ->whereNull('mentor_id')
            ->whereNull('filed')
            ->whereNull('cancelled_datetime')
            ->exists();
    }

    private function getAvailabilities(): Collection
    {
        $member = $this->trainingPlace->account->member;

        if (! $member) {
            return collect();
        }

        return Availability::where('student_id', $member->id)
            ->where('date', '>=', now()->format('Y-m-d'))
            ->orderBy('date')
            ->orderBy('from')
            ->limit(20)
            ->get();
    }

    public function render()
    {
        return view('livewire.training.student-availability-table');
    }
}
