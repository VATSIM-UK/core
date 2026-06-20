<?php

namespace App\Livewire\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\MentoringSessionsService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
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
                Action::make('bookSession')
                    ->label('Book Session')
                    ->color('primary')
                    ->visible(fn () => $this->hasPendingSession())
                    ->modalHeading(fn (Availability $record) => "Book Mentoring Session: {$this->trainingPlace->account->name}")
                    ->modalDescription(fn (Availability $record) => 'You are booking a mentoring session. Please confirm the exact start and end times below.')
                    ->modalSubmitActionLabel('Book Session')
                    ->form(function (Availability $record) {
                        $student = Member::findOrFail($record->student_id);
                        $callsign = $this->trainingPlace->trainingPosition?->cts_primary_position
                            ?? $this->trainingPlace->trainingPosition?->position?->callsign;

                        $minTime = Carbon::parse($record->from)->format('H:i');
                        $maxTime = Carbon::parse($record->to)->format('H:i');
                        $timeOptions = $this->generateTimeOptions($minTime, $maxTime);

                        if (Carbon::parse($record->date)->isToday()) {
                            $nowTime = now()->format('H:i');
                            $timeOptions = array_filter($timeOptions, fn ($time) => $time >= $nowTime, ARRAY_FILTER_USE_KEY);
                        }

                        return [
                            Grid::make(3)->schema([
                                Placeholder::make('student_name')
                                    ->label('Student')
                                    ->content($student->name),

                                Placeholder::make('student_cid')
                                    ->label('CID')
                                    ->content($student->cid),

                                Placeholder::make('position')
                                    ->label('Position')
                                    ->content($callsign ?? 'N/A'),
                            ]),

                            Grid::make(2)->schema([
                                Select::make('taken_from')
                                    ->label('Start')
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->searchPrompt('Type a time (e.g. 18:30)')
                                    ->options($timeOptions)
                                    ->default(array_key_first($timeOptions))
                                    ->optionsLimit(100),

                                Select::make('taken_to')
                                    ->label('End')
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->searchPrompt('Type a time (e.g. 18:30)')
                                    ->after('taken_from')
                                    ->options(function (Get $get) use ($timeOptions) {
                                        $startTime = $get('taken_from');
                                        if (! $startTime) {
                                            return $timeOptions;
                                        }

                                        [$startH, $startM] = explode(':', $startTime);
                                        $startMinutes = (int) $startH * 60 + (int) $startM;
                                        $minEndMinutes = $startMinutes + 45;

                                        return collect($timeOptions)
                                            ->filter(function ($label, $key) use ($minEndMinutes) {
                                                [$h, $m] = explode(':', $key);
                                                $keyMinutes = (int) $h * 60 + (int) $m;

                                                return $keyMinutes >= $minEndMinutes;
                                            })
                                            ->toArray();
                                    })
                                    ->default(array_key_last($timeOptions))
                                    ->optionsLimit(100),
                            ]),

                            Callout::make('slot_in_past')
                                ->heading('This availability slot is in the past')
                                ->description('The student\'s availability window for this slot has already expired.')
                                ->danger()
                                ->visible(fn () => Carbon::parse($record->date)->setTimeFromTimeString(Carbon::parse($record->to)->format('H:i'))->isPast()),

                            Callout::make('24_hours_notice')
                                ->heading('Less than 24 hours notice')
                                ->description('This session is being booked with less than 24 hours notice. Please contact the student via Discord to confirm their attendance.')
                                ->warning()
                                ->visible(function (Get $get) use ($record) {
                                    $selectedTime = $get('taken_from');
                                    if (! $selectedTime) {
                                        return false;
                                    }

                                    $sessionStart = Carbon::parse($record->date)->setTimeFromTimeString($selectedTime);

                                    return $sessionStart->isAfter(now()) && now()->diffInHours($sessionStart, false) < 24;
                                }),
                        ];
                    })
                    ->action(function (array $data, Availability $record, MentoringSessionsService $mentoringService) {
                        $this->bookSession($data, $record, $mentoringService);
                    }),
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

    private function bookSession(array $data, Availability $availability, MentoringSessionsService $mentoringService): void
    {
        $from = Carbon::parse($data['taken_from']);
        $to = Carbon::parse($data['taken_to']);

        if ($from->diffInMinutes($to) < 45) {
            Notification::make()
                ->title('Session Too Short')
                ->body('The session must be at least 45 minutes long.')
                ->danger()
                ->send();

            return;
        }

        $callsign = $this->trainingPlace->trainingPosition?->cts_primary_position
            ?? $this->trainingPlace->trainingPosition?->position?->callsign;

        if (! $callsign) {
            Notification::make()
                ->title('Booking Failed')
                ->body('No position callsign found for this training place.')
                ->danger()
                ->send();

            return;
        }

        if (! $this->hasPendingSession()) {
            Notification::make()
                ->title('No Pending Session')
                ->body('This student does not have a pending session request. They may have been forwarded for an exam.')
                ->warning()
                ->send();

            return;
        }

        $success = $mentoringService->acceptSession(
            $availability->id,
            Auth::user(),
            $data['taken_from'],
            $data['taken_to']
        );

        if ($success) {
            Notification::make()
                ->title('Session Booked')
                ->body('Mentoring session has been booked.')
                ->success()
                ->send();

            $this->dispatch('session-booked');

            return;
        }

        Notification::make()
            ->title('Booking Failed')
            ->body('Could not accept the mentoring session. The slot may have been cancelled or already claimed.')
            ->danger()
            ->send();
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

    protected function generateTimeOptions(?string $minTime = null, ?string $maxTime = null): array
    {
        $options = [];

        $minMinutes = $minTime ? (int) substr($minTime, 0, 2) * 60 + (int) substr($minTime, 3, 2) : 0;
        $maxMinutes = $maxTime ? (int) substr($maxTime, 0, 2) * 60 + (int) substr($maxTime, 3, 2) : 1440;

        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 15) {
                $currentMinutes = $h * 60 + $m;

                if ($currentMinutes >= $minMinutes && $currentMinutes <= $maxMinutes) {
                    $time = sprintf('%02d:%02d', $h, $m);
                    $options[$time] = $time;
                }
            }
        }

        if ($minTime && ! isset($options[$minTime])) {
            $options[$minTime] = $minTime;
        }

        if ($maxTime && ! isset($options[$maxTime])) {
            $options[$maxTime] = $maxTime;
        }

        ksort($options);

        return $options;
    }

    public function render()
    {
        return view('livewire.training.student-availability-table');
    }
}
