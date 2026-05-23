<?php

namespace App\Livewire\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Session;
use App\Services\Training\MentoringSessionsService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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
                ActionGroup::make([
                    Action::make('reschedule')
                        ->label('Reschedule')
                        ->icon('heroicon-m-clock')
                        ->color('warning')
                        ->modalHeading(fn (Session $record) => "Reschedule Session: {$record->student->name}")
                        ->modalSubmitActionLabel('Reschedule Session')
                        ->form([
                            Select::make('selected_availability_id')
                                ->label('Student Availability Slot')
                                ->required()
                                ->live()
                                ->options(function (Session $record) {
                                    return Availability::query()
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
                                        });
                                })
                                ->afterStateUpdated(function (Set $set, $state) {
                                    $newAvail = Availability::find($state);
                                    if ($newAvail) {
                                        $set('taken_from', Carbon::parse($newAvail->from)->format('H:i'));
                                        $set('taken_to', Carbon::parse($newAvail->to)->format('H:i'));
                                    } else {
                                        $set('taken_from', null);
                                        $set('taken_to', null);
                                    }
                                }),

                            Grid::make(2)->schema([
                                Select::make('taken_from')
                                    ->label('New Start Time')
                                    ->required()
                                    ->searchable()
                                    ->allowHtml(false)
                                    ->optionsLimit(100)
                                    ->options(function (Get $get) {
                                        $avail = Availability::find($get('selected_availability_id'));
                                        if (! $avail) {
                                            return [];
                                        }

                                        return $this->generateTimeOptions(
                                            Carbon::parse($avail->from)->format('H:i'),
                                            Carbon::parse($avail->to)->format('H:i')
                                        );
                                    }),

                                Select::make('taken_to')
                                    ->label('New End Time')
                                    ->required()
                                    ->searchable()
                                    ->allowHtml(false)
                                    ->optionsLimit(100)
                                    ->options(function (Get $get) {
                                        $avail = Availability::find($get('selected_availability_id'));
                                        if (! $avail) {
                                            return [];
                                        }

                                        return $this->generateTimeOptions(
                                            Carbon::parse($avail->from)->format('H:i'),
                                            Carbon::parse($avail->to)->format('H:i')
                                        );
                                    }),
                            ]),
                        ])
                        ->action(function (array $data, Session $record, MentoringSessionsService $mentoringService) {
                            $success = $mentoringService->rescheduleSession(
                                $record->id,
                                $data['selected_availability_id'],
                                $data['taken_from'],
                                $data['taken_to']
                            );

                            if ($success) {
                                $avail = Availability::find($data['selected_availability_id']);
                                $dateFormatted = Carbon::parse($avail->date)->format('d/m/Y');

                                Notification::make()
                                    ->title('Session Rescheduled')
                                    ->body("The session with {$record->student->name} has been moved to {$dateFormatted} from {$data['taken_from']} to {$data['taken_to']}.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Could not reschedule the session.')
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('cancel')
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
                            $memberId = auth()->user()->member->id;

                            $success = $mentoringService->cancelSession($record->id, $data['reason'], $memberId);

                            if ($success) {
                                Notification::make()
                                    ->title('Session Cancelled')
                                    ->body("The session with {$record->student->name} has been successfully cancelled.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Error')
                                    ->body('Could not cancel the session.')
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Manage Session'),
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

    public function render()
    {
        return view('livewire.training.accepted-mentoring-sessions-table');
    }
}
