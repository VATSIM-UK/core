<?php

namespace App\Livewire\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\PracticalExaminers;
use App\Repositories\Cts\ExaminerRepository;
use Carbon\Carbon;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Livewire\Component;

class ExamRequestsTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected $listeners = ['exam-accepted' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Exam Requests')
            ->description('Pending exam requests that need examiner assignment will be displayed here.')
            ->query($this->getFilteredExamRequestsQuery())
            ->columns([
                TextColumn::make('student.cid')->label('CID'),
                TextColumn::make('student.name')->label('Name'),
                TextColumn::make('exam')->label('Level'),
                TextColumn::make('position_1')->label('Position'),
            ])
            ->actions([
                Action::make('Accept')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn (ExamBooking $examBooking) => $examBooking->finished != ExamBooking::FINISHED_FLAG &&
                        $this->canConductExam($examBooking->exam)
                    )
                    ->form([
                        Select::make('availability_slot')
                            ->label('Select Availability Slot')
                            ->required()
                            ->options(function (ExamBooking $record) {
                                return Availability::where('student_id', $record->student_id)
                                    ->where('type', 'S')
                                    ->where('date', '>=', now()->toDateString())
                                    ->orderBy('date')
                                    ->orderBy('from')
                                    ->get()
                                    ->mapWithKeys(function ($availability) {
                                        $date = $availability->date->format('Y-m-d');
                                        $fromTime = Carbon::parse($availability->from)->format('H:i');
                                        $toTime = Carbon::parse($availability->to)->format('H:i');

                                        return [
                                            $availability->id => "{$date} from {$fromTime} to {$toTime}",
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->placeholder('Select an availability slot')
                            ->helperText('Choose from the student\'s available time slots')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset time fields when availability slot changes
                                $set('start_hour', null);
                                $set('start_minute', null);
                                $set('end_hour', null);
                                $set('end_minute', null);
                            }),

                        Grid::make(2)
                            ->visible(fn (Get $get) => $get('availability_slot'))
                            ->schema([
                                Select::make('start_hour')
                                    ->label('Start Hour')
                                    ->required()
                                    ->options(function (Get $get) {
                                        $availability = $this->getAvailabilityFromForm($get);
                                        if (! $availability) {
                                            return [];
                                        }

                                        $startHour = Carbon::parse($availability->from)->hour;
                                        $endHour = Carbon::parse($availability->to)->hour;

                                        return $this->generateHourOptions($startHour, $endHour);
                                    })
                                    ->placeholder('Hour')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $this->resetSubsequentTimeFields($set, ['start_minute', 'end_hour', 'end_minute']);
                                    }),

                                Select::make('start_minute')
                                    ->label('Start Minute')
                                    ->required()
                                    ->options(function (Get $get) {
                                        $availability = $this->getAvailabilityFromForm($get);
                                        $selectedHour = $get('start_hour');

                                        if (! $availability || ! is_numeric($selectedHour)) {
                                            return [];
                                        }

                                        $availStart = Carbon::parse($availability->from);
                                        $availEnd = Carbon::parse($availability->to);

                                        return $this->generateStartMinuteOptions($selectedHour, $availStart, $availEnd);
                                    })
                                    ->placeholder('Minute')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $this->resetSubsequentTimeFields($set, ['end_hour', 'end_minute']);
                                    }),
                            ]),

                        Grid::make(2)
                            ->visible(fn (Get $get) => $get('availability_slot') && $get('start_hour') !== null && $get('start_minute') !== null)
                            ->schema([
                                Select::make('end_hour')
                                    ->label('End Hour')
                                    ->required()
                                    ->options(function (Get $get) {
                                        $availability = $this->getAvailabilityFromForm($get);
                                        $startHour = $get('start_hour');
                                        $startMinute = $get('start_minute');

                                        if (! $availability || ! is_numeric($startHour) || ! is_numeric($startMinute)) {
                                            return [];
                                        }

                                        $startTime = Carbon::create(null, null, null, $startHour, $startMinute);
                                        $minEndTime = $startTime->copy()->addMinutes(60); // Minimum 60 minutes
                                        $maxEndTime = $startTime->copy()->addMinutes(120); // Maximum 120 minutes (2 hours)
                                        $availEnd = Carbon::parse($availability->to);

                                        // Use the earliest of max exam duration or availability end
                                        $effectiveEndTime = $maxEndTime->lessThan($availEnd) ? $maxEndTime : $availEnd;

                                        return $this->generateHourOptions($minEndTime->hour, $effectiveEndTime->hour);
                                    })
                                    ->placeholder('Hour')
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $this->resetSubsequentTimeFields($set, ['end_minute']);
                                    }),

                                Select::make('end_minute')
                                    ->label('End Minute')
                                    ->required()
                                    ->options(function (Get $get) {
                                        $availability = $this->getAvailabilityFromForm($get);
                                        $startHour = $get('start_hour');
                                        $startMinute = $get('start_minute');
                                        $endHour = $get('end_hour');

                                        if (! $availability || ! is_numeric($startHour) || ! is_numeric($startMinute) || ! is_numeric($endHour)) {
                                            return [];
                                        }

                                        $startTime = Carbon::create(null, null, null, $startHour, $startMinute);
                                        $minEndTime = $startTime->copy()->addMinutes(60);
                                        $maxEndTime = $startTime->copy()->addMinutes(120); // Maximum 120 minutes (2 hours)
                                        $availEnd = Carbon::parse($availability->to);

                                        // Use the earliest of max exam duration or availability end
                                        $effectiveEndTime = $maxEndTime->lessThan($availEnd) ? $maxEndTime : $availEnd;

                                        return $this->generateEndMinuteOptions($endHour, $minEndTime, $effectiveEndTime);
                                    })
                                    ->placeholder('Minute'),
                            ]),

                        Select::make('secondary_examiner')
                            ->label(function (ExamBooking $record) {
                                return $this->isSecondaryExaminerRequired($record->exam)
                                    ? 'Secondary Examiner (Required)'
                                    : 'Secondary Examiner (Optional)';
                            })
                            ->placeholder('Select secondary examiner')
                            ->required(function (ExamBooking $record) {
                                return $this->isSecondaryExaminerRequired($record->exam);
                            })
                            ->options(
                                (new ExaminerRepository)
                                    ->getAtcExaminers()
                                    ->mapWithKeys(function ($examiner) {
                                        return [$examiner->id => $examiner->name];
                                    })
                            ),
                    ])
                    ->action(function (array $data, ExamBooking $record) {
                        // Get the selected availability slot
                        $availability = Availability::find($data['availability_slot']);

                        if (! $availability) {
                            throw new \Exception('Selected availability slot not found.');
                        }

                        // Create time strings from hour/minute components
                        $startTime = sprintf('%02d:%02d:00', $data['start_hour'], $data['start_minute']);
                        $endTime = sprintf('%02d:%02d:00', $data['end_hour'], $data['end_minute']);

                        // Combine the availability date with the selected times
                        $availabilityDate = $availability->date->format('Y-m-d');
                        $examStartDateTime = Carbon::parse("{$availabilityDate} {$startTime}");
                        $examEndDateTime = Carbon::parse("{$availabilityDate} {$endTime}");

                        // Validate exam duration constraints
                        $durationMinutes = $examStartDateTime->diffInMinutes($examEndDateTime);

                        if ($durationMinutes < 60) {
                            throw new \Exception('Exam duration must be at least 60 minutes.');
                        }

                        if ($durationMinutes > 120) {
                            throw new \Exception('Exam duration cannot exceed 120 minutes (2 hours).');
                        }

                        // Secondary examiner validation is handled by client-side form validation

                        $examBooking = $record->load('examiners');
                        $examBooking->update([
                            'taken' => 1,
                            'taken_date' => $examStartDateTime->format('Y-m-d'),
                            'taken_from' => $examStartDateTime->format('H:i:s'),
                            'taken_to' => $examEndDateTime->format('H:i:s'),
                            'exmr_id' => auth()->user()->member->id,
                            'exmr_rating' => auth()->user()->member->account->qualification_atc->vatsim,
                            'time_book' => now(),
                            'second_examiner_req' => $this->isSecondaryExaminerRequired($record->exam) || ! empty($data['secondary_examiner']) ? 1 : 0,
                        ]);

                        PracticalExaminers::create([
                            'examid' => $examBooking->id,
                            'senior' => auth()->user()->member->id,
                            'other' => $data['secondary_examiner'],
                        ]);

                        $studentName = $examBooking->student->name;
                        $examDateTime = $examStartDateTime->format('Y-m-d H:i');

                        Notification::make()
                            ->title('Exam Accepted')
                            ->success()
                            ->body("Exam accepted for {$studentName} at {$examDateTime}")
                            ->send();

                        $this->dispatch('exam-accepted');
                    }),
            ]);
    }

    /**
     * Get availability record from form state
     */
    protected function getAvailabilityFromForm(Get $get): ?Availability
    {
        $availabilityId = $get('availability_slot');
        if (! $availabilityId) {
            return null;
        }

        return Availability::find($availabilityId);
    }

    /**
     * Generate hour options array with formatted values
     */
    protected function generateHourOptions(int $startHour, int $endHour): array
    {
        $hours = [];
        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $hours[$hour] = sprintf('%02d', $hour);
        }

        return $hours;
    }

    /**
     * Generate minute options within availability window (exclusive end time)
     */
    protected function generateStartMinuteOptions(int $hour, Carbon $startTime, Carbon $endTime): array
    {
        $minutes = [];
        for ($minute = 0; $minute <= 45; $minute += 15) {
            $testTime = Carbon::create(null, null, null, $hour, $minute);

            // Only include minutes that are within the availability window (exclusive end)
            if ($testTime->greaterThanOrEqualTo($startTime) &&
                $testTime->lessThan($endTime)) {
                $minutes[$minute] = sprintf('%02d', $minute);
            }
        }

        return $minutes;
    }

    /**
     * Generate minute options within availability window (inclusive end time)
     */
    protected function generateEndMinuteOptions(int $hour, Carbon $startTime, Carbon $endTime): array
    {
        $minutes = [];
        for ($minute = 0; $minute <= 45; $minute += 15) {
            $testTime = Carbon::create(null, null, null, $hour, $minute);

            // Only include minutes that are within the availability window (inclusive end)
            if ($testTime->greaterThanOrEqualTo($startTime) &&
                $testTime->lessThanOrEqualTo($endTime)) {
                $minutes[$minute] = sprintf('%02d', $minute);
            }
        }

        return $minutes;
    }

    /**
     * Reset subsequent time fields when a time selection changes
     */
    protected function resetSubsequentTimeFields(callable $set, array $fields): void
    {
        foreach ($fields as $field) {
            $set($field, null);
        }
    }

    /**
     * Get filtered exam requests query based on user's exam-level permissions
     */
    protected function getFilteredExamRequestsQuery()
    {
        $query = ExamBooking::query()
            ->with(['student', 'examiners'])
            ->where('taken', 0) // Not yet taken/accepted
            ->whereDoesntHave('examiners');

        // Filter by exam levels the user has permission to conduct
        $allowedExamLevels = $this->getAllowedExamLevels();

        if (empty($allowedExamLevels)) {
            // If user has no exam conduct permissions, return empty query
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('exam', $allowedExamLevels);
    }

    /**
     * Get all exam levels the current user has permission to conduct
     */
    protected function getAllowedExamLevels(): array
    {
        $examLevels = ['OBS', 'TWR', 'APP', 'CTR']; // Common exam levels
        $allowedLevels = [];

        foreach ($examLevels as $level) {
            if ($this->canConductExam($level)) {
                // Include both uppercase and lowercase versions to handle case insensitive matches
                $allowedLevels[] = $level;
                $allowedLevels[] = strtolower($level);
            }
        }

        return array_unique($allowedLevels);
    }

    /**
     * Check if the current user can conduct an exam of the given level
     */
    protected function canConductExam(string $examLevel): bool
    {
        $permissionSafeExamLevel = Str::lower($examLevel);

        return auth()->user()->can("training.exams.conduct.{$permissionSafeExamLevel}");
    }

    /**
     * Check if a secondary examiner is required for the given exam level
     */
    protected function isSecondaryExaminerRequired(string $examLevel): bool
    {
        $levelUpper = Str::upper($examLevel);

        return in_array($levelUpper, ['APP', 'CTR']);
    }

    public function render()
    {
        return view('livewire.training.exam-requests-table');
    }
}
