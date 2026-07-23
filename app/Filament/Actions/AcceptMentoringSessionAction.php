<?php

namespace App\Filament\Actions;

use App\Models\Cts\Availability;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Services\Training\MentoringSessionsService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;

class AcceptMentoringSessionAction
{
    public static function make(
        string $name = 'acceptSession',
        string $label = 'Accept Session',
        string $color = 'primary',
        string|callable|null $modalHeading = null,
        string|callable|null $modalDescription = null,
        string $modalSubmitActionLabel = 'Accept Session',
        ?callable $resolveAvailability = null,
        ?callable $resolvePosition = null,
        ?callable $visibilityCondition = null,
        ?callable $onSuccess = null,
    ): Action {
        $resolveAvailability ??= fn (array $arguments, $record = null): Availability => $record ?? Availability::findOrFail($arguments['availability_id']);

        return Action::make($name)
            ->label($label)
            ->color($color)
            ->visible($visibilityCondition ?? fn (): bool => true)
            ->modalHeading($modalHeading ?? fn (): string => 'Accept Mentoring Session')
            ->modalDescription($modalDescription ?? fn (): string => 'Please confirm the exact start and end times below.')
            ->modalSubmitActionLabel($modalSubmitActionLabel)
            ->form(function (array $arguments, mixed $record = null) use ($resolveAvailability, $resolvePosition) {
                $availability = $resolveAvailability($arguments, $record);
                $student = Member::findOrFail($availability->student_id);

                $pendingSession = Session::query()
                    ->where('student_id', $student->id)
                    ->whereNull('mentor_id')
                    ->whereNull('filed')
                    ->whereNull('cancelled_datetime')
                    ->first();

                $position = $resolvePosition
                    ? $resolvePosition($availability, $student, $pendingSession)
                    : ($pendingSession?->position ?? 'N/A');

                $minTime = Carbon::parse($availability->from)->format('H:i');
                $maxTime = Carbon::parse($availability->to)->format('H:i');
                $timeOptions = static::generateTimeOptions($minTime, $maxTime);

                if (Carbon::parse($availability->date)->isToday()) {
                    $nowTime = now()->format('H:i');
                    $timeOptions = array_filter($timeOptions, fn ($time) => $time >= $nowTime, ARRAY_FILTER_USE_KEY);
                }

                return [
                    Grid::make(3)->schema([
                        Placeholder::make('student_name')
                            ->label('Student Name')
                            ->content($student->name),

                        Placeholder::make('student_cid')
                            ->label('Student CID')
                            ->content($student->cid),

                        Placeholder::make('position')
                            ->label('Position')
                            ->content($position),
                    ]),

                    Grid::make(2)->schema([
                        Select::make('taken_from')
                            ->label('Start')
                            ->required()
                            ->searchable()
                            ->live()
                            ->allowHtml(false)
                            ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                            ->options($timeOptions)
                            ->default(array_key_first($timeOptions))
                            ->optionsLimit(100),

                        Select::make('taken_to')
                            ->label('End')
                            ->required()
                            ->searchable()
                            ->allowHtml(false)
                            ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
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
                        ->description('The student\'s availability window for this slot has already expired. You won\'t be able to accept a session during this slot.')
                        ->danger()
                        ->visible(function () use ($availability) {
                            $slotEnd = Carbon::parse($availability->date)
                                ->setTimeFromTimeString(Carbon::parse($availability->to)->format('H:i'));

                            return $slotEnd->isBefore(now());
                        }),

                    Callout::make('24_hours_notice')
                        ->heading('This session is being booked with less than 24 hours notice')
                        ->description('Please contact the student via Discord to confirm their attendance.')
                        ->warning()
                        ->visible(function (Get $get) use ($availability) {
                            $selectedTime = $get('taken_from');

                            if (! $selectedTime) {
                                return false;
                            }

                            $sessionStart = Carbon::parse($availability->date)->setTimeFromTimeString($selectedTime);

                            return $sessionStart->isAfter(now()) && now()->diffInHours($sessionStart, false) < 24;
                        }),

                    Callout::make('overlapping_booking')
                        ->heading(function (Get $get) use ($availability, $pendingSession) {
                            $overlap = static::getOverlappingBooking($get, $availability, $pendingSession);

                            if (! $overlap) {
                                return '';
                            }

                            return $overlap instanceof Session ? 'Overlapping Session Detected' : 'Overlapping Exam Detected';
                        })
                        ->description(function (Get $get) use ($availability, $pendingSession) {
                            $overlap = static::getOverlappingBooking($get, $availability, $pendingSession);

                            if (! $overlap) {
                                return '';
                            }

                            $type = $overlap instanceof Session ? 'session' : 'exam';
                            $from = $overlap->taken_from;
                            $to = $overlap->taken_to;

                            return "There is already a {$type} booked on this position from {$from} to {$to}.";
                        })
                        ->danger()
                        ->visible(function (Get $get) use ($availability, $pendingSession) {
                            return static::getOverlappingBooking($get, $availability, $pendingSession) !== null;
                        }),
                ];
            })
            ->action(function (array $data, array $arguments, mixed $record = null, MentoringSessionsService $mentoringService) use ($resolveAvailability, $onSuccess) {
                $availability = $resolveAvailability($arguments, $record);
                $student = Member::findOrFail($availability->student_id);
                $formattedDate = Carbon::parse($availability->date)->format('d/m/Y');

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

                $success = $mentoringService->acceptSession($availability->id, auth()->user(), $data['taken_from'], $data['taken_to']);

                if ($success) {
                    Notification::make()
                        ->title('Session Accepted Successfully')
                        ->body("You have now accepted a session to mentor {$student->name} on {$formattedDate} from {$data['taken_from']} to {$data['taken_to']}.")
                        ->success()
                        ->send();

                    if ($onSuccess) {
                        $onSuccess();
                    }

                    return;
                }

                Notification::make()
                    ->title('Booking Failed')
                    ->body('We could not find an active pending session request for this slot. It may have been cancelled or already claimed.')
                    ->danger()
                    ->send();
            });
    }

    protected static function getOverlappingBooking(Get $get, Availability $availability, ?Session $pendingSession): Session|ExamBooking|null
    {
        $takenFrom = $get('taken_from');
        $takenTo = $get('taken_to');

        if (! $takenFrom || ! $takenTo || ! $pendingSession) {
            return null;
        }

        return app(MentoringSessionsService::class)->checkForOverlappingBookings(
            $pendingSession->position,
            $availability->date,
            $takenFrom,
            $takenTo,
            $pendingSession->id
        );
    }

    protected static function generateTimeOptions(?string $minTime = null, ?string $maxTime = null): array
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
}
