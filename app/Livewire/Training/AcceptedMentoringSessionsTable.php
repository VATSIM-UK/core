<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Mentor\ConductMentoringSession;
use App\Models\Cts\Availability;
use App\Models\Cts\Session;
use App\Services\Training\MentoringAnnouncementService;
use App\Services\Training\MentoringReportService;
use App\Services\Training\MentoringSessionsService;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
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
            ->actions([
                Action::make('conduct')
                    ->label('Conduct Session')
                    ->url(fn (Session $record): string => ConductMentoringSession::getUrl(['sessionId' => $record->id]))
                    ->visible(fn (Session $record): bool => auth()->user()?->can('conduct', $record) ?? false),

                ActionGroup::make([
                    ActionGroup::make([
                        $this->postMentoringSessionAnnouncementAction(),
                        $this->markNoShowTableAction(),
                    ])->dropdown(false),

                    ActionGroup::make([
                        $this->rescheduleSessionAction(),
                        $this->cancelSessionAction(),
                    ])->dropdown(false),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Session Actions'),
            ])
            ->emptyStateHeading('No upcoming mentoring sessions found');
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

    protected function markNoShowTableAction(): Action
    {
        return Action::make('markNoShow')
            ->label('Mark no-show')
            ->color('danger')
            ->icon('heroicon-o-user-minus')
            ->visible(fn (Session $record): bool => app(MentoringReportService::class)->canMarkNoShow($record))
            ->requiresConfirmation()
            ->modalHeading('Mark session as no-show')
            ->modalDescription(fn (Session $record) => app(MentoringReportService::class)->wasBookedWithShortNotice($record)
                ? 'This session was booked with less than 24 hours notice. Did the student confirm their non-attendance via Discord?'
                : 'Are you sure you want to mark this session as a no-show? The report will be filed automatically.')
            ->schema(fn (Session $record) => app(MentoringReportService::class)->wasBookedWithShortNotice($record)
                ? [
                    Toggle::make('student_confirmed_discord')
                        ->label('Student confirmed non-attendance via Discord')
                        ->required(),
                ]
                : [])
            ->action(function (Session $record, array $data, MentoringReportService $service): void {
                $wasShortNotice = $service->wasBookedWithShortNotice($record);
                $confirmed = (bool) ($data['student_confirmed_discord'] ?? false);

                try {
                    $service->markNoShow($record, $confirmed);
                } catch (ValidationException $exception) {
                    Notification::make()
                        ->title('Unable to mark no-show')
                        ->body(collect($exception->errors())->flatten()->first())
                        ->danger()
                        ->send();

                    return;
                }

                if ($wasShortNotice && ! $confirmed) {
                    Notification::make()
                        ->title('Session cancelled')
                        ->body('The session was cancelled on your behalf. No no-show has been recorded for the student.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Session marked as no-show')
                        ->success()
                        ->send();
                }
            });
    }

    protected function postMentoringSessionAnnouncementAction(): Action
    {
        return Action::make('postMentoringAnnouncement')
            ->label('Post Mentoring Announcement')
            ->icon('heroicon-o-megaphone')
            ->color('info')
            ->visible(function (Session $record): bool {
                $category = app(MentorPermissionService::class)->resolveCategoryForCtsCallsign($record->position);

                if ($category === 'OBS to S1 Training') {
                    return false;
                }

                return app(MentoringAnnouncementService::class)->canPostAnnouncement($record, auth()->user()->member->id);
            })
            ->form([
                Checkbox::make('ping_pilot')
                    ->label('Ping: Pilot Role')
                    ->default(true),

                Checkbox::make('ping_controller')
                    ->label('Ping: Controller Role')
                    ->default(false),

                Textarea::make('notes')
                    ->label('Additional notes')
                    ->placeholder('Optional: additional notes')
                    ->rows(4)
                    ->maxLength(1000),
            ])
            ->requiresConfirmation()
            ->action(function (Session $record, array $data): void {
                try {
                    app(MentoringAnnouncementService::class)->postAnnouncement($record, $data);

                    Notification::make()
                        ->title('Discord notification sent')
                        ->success()
                        ->send();
                } catch (\Throwable) {
                    Notification::make()
                        ->title('Failed to post to Discord')
                        ->danger()
                        ->send();
                }
            });
    }

    private function rescheduleSessionAction(): Action
    {
        return Action::make('reschedule')
            ->label('Reschedule Session')
            ->icon('heroicon-m-clock')
            ->color('warning')
            ->modalHeading(fn (Session $record) => "Reschedule Session: {$record->student->name}")
            ->modalSubmitActionLabel('Reschedule Session')
            ->form([
                Select::make('selected_availability_id')
                    ->label('Student Availability Slot')
                    ->required()
                    ->live()
                    ->options(fn (Session $record) => Availability::query()
                        ->where('student_id', $record->student_id)
                        ->whereDate('date', '>=', now())
                        ->orderBy('date')
                        ->orderBy('from')
                        ->get()
                        ->mapWithKeys(function ($avail) {
                            $date = Carbon::parse($avail->date)->format('D, d M Y');
                            $start = Carbon::parse($avail->from)->format('H:i');
                            $end = Carbon::parse($avail->to)->format('H:i');

                            return [$avail->id => "{$date} ({$start} to {$end})"];
                        })
                    )
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (! $state || ! $newAvail = Availability::find($state)) {
                            $set('taken_from', null);
                            $set('taken_to', null);

                            return;
                        }

                        $minTime = Carbon::parse($newAvail->from)->format('H:i');
                        $maxTime = Carbon::parse($newAvail->to)->format('H:i');
                        $options = $this->generateTimeOptions($minTime, $maxTime);

                        if (Carbon::parse($newAvail->date)->isToday()) {
                            $nowTime = now()->format('H:i');
                            $options = array_filter($options, fn ($time) => $time >= $nowTime, ARRAY_FILTER_USE_KEY);
                        }

                        $set('taken_from', array_key_first($options));
                        $set('taken_to', array_key_last($options));
                    }),

                Grid::make(2)->schema([
                    Select::make('taken_from')
                        ->label('New Start Time')
                        ->required()
                        ->searchable()
                        ->allowHtml(false)
                        ->live()
                        ->optionsLimit(100)
                        ->options(function (Get $get) {
                            if (! $availId = $get('selected_availability_id')) {
                                return [];
                            }

                            $avail = Availability::find($availId);
                            if (! $avail) {
                                return [];
                            }

                            $options = $this->generateTimeOptions(
                                Carbon::parse($avail->from)->format('H:i'),
                                Carbon::parse($avail->to)->format('H:i')
                            );

                            if (Carbon::parse($avail->date)->isToday()) {
                                $nowTime = now()->format('H:i');
                                $options = array_filter($options, fn ($time) => $time >= $nowTime, ARRAY_FILTER_USE_KEY);
                            }

                            return $options;
                        }),

                    Select::make('taken_to')
                        ->label('New End Time')
                        ->required()
                        ->searchable()
                        ->allowHtml(false)
                        ->optionsLimit(100)
                        ->options(function (Get $get) {
                            if (! $availId = $get('selected_availability_id')) {
                                return [];
                            }

                            $avail = Availability::find($availId);
                            if (! $avail) {
                                return [];
                            }

                            $options = $this->generateTimeOptions(
                                Carbon::parse($avail->from)->format('H:i'),
                                Carbon::parse($avail->to)->format('H:i')
                            );

                            $startTime = $get('taken_from');
                            if (! $startTime) {
                                return $options;
                            }

                            return collect($options)
                                ->filter(fn ($label, $key) => $key > $startTime)
                                ->toArray();
                        }),
                ]),
            ])
            ->action(function (array $data, Session $record, MentoringSessionsService $mentoringService) {
                $availability = Availability::find($data['selected_availability_id']);

                if (! $availability) {
                    Notification::make()
                        ->title('Reschedule Failed')
                        ->body('The selected availability slot could no longer be found.')
                        ->danger()
                        ->send();

                    return;
                }

                $proposedStart = Carbon::parse($availability->date)->setTimeFromTimeString($data['taken_from']);
                if ($proposedStart->isPast()) {
                    Notification::make()
                        ->title('Invalid Time Chosen')
                        ->body('You cannot reschedule a mentoring session to a time that has already passed.')
                        ->danger()
                        ->send();

                    return;
                }

                $success = $mentoringService->rescheduleSession(
                    $record->id,
                    $availability->id,
                    $data['taken_from'],
                    $data['taken_to'],
                    auth()->user(),
                );

                if ($success) {
                    $dateFormatted = Carbon::parse($availability->date)->format('d/m/Y');

                    Notification::make()
                        ->title('Session Rescheduled')
                        ->body("The session with {$record->student->name} has been moved to {$dateFormatted} from {$data['taken_from']} to {$data['taken_to']}.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Error')
                        ->body('Could not reschedule the session. Please confirm the request is still valid.')
                        ->danger()
                        ->send();
                }
            });
    }

    private function cancelSessionAction(): Action
    {
        return Action::make('cancel')
            ->label('Cancel Session')
            ->icon('heroicon-m-x-circle')
            ->color('danger')
            ->modalHeading(fn (Session $record) => "Cancel Session: {$record->student->name}")
            ->modalDescription('Please provide a reason for cancelling this session. This will be recorded and visible to the student.')
            ->modalSubmitActionLabel('Cancel Session')
            ->form([
                Textarea::make('reason')
                    ->label('Cancellation Reason')
                    ->required()
                    ->minLength(10),
            ])
            ->action(function (array $data, Session $record, MentoringSessionsService $mentoringService) {
                $success = $mentoringService->cancelSession($record->id, $data['reason'], auth()->user());

                if ($success) {
                    Notification::make()
                        ->title('Session Cancelled')
                        ->body("The session with {$record->student->name} has been successfully cancelled.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Cancellation Failed')
                        ->body('Could not cancel the session. It may have already been modified or completed.')
                        ->danger()
                        ->send();
                }
            });
    }

    public function render()
    {
        return view('livewire.training.accepted-mentoring-sessions-table');
    }
}
