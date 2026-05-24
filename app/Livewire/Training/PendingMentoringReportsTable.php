<?php

declare(strict_types=1);

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Mentor\ConductMentoringSession;
use App\Models\Cts\Session;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentoringReportService;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class PendingMentoringReportsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public string $category = '';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pending Reports')
            ->description('Accepted sessions that have taken place but do not yet have a filed mentoring report.')
            ->query(
                (new SessionRepository)->getPendingReportSessionsForPositionsQuery(
                    $this->getVisibleCtsPositions()
                )
            )
            ->defaultSort('taken_date', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->getStateUsing(fn (Session $record) => $record->student->name)
                    ->description(fn (Session $record) => $record->student->cid),

                TextColumn::make('mentor_name')
                    ->label('Mentor')
                    ->getStateUsing(fn (Session $record) => $record->mentor->name)
                    ->description(fn (Session $record) => $record->mentor->cid),

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
            ->recordActions([
                Action::make('conduct')
                    ->label('Conduct')
                    ->url(fn (Session $record): string => ConductMentoringSession::getUrl(['sessionId' => $record->id]))
                    ->visible(fn (Session $record): bool => auth()->user()?->can('conduct', $record) ?? false),
                Action::make('markNoShow')
                    ->label('Mark no-show')
                    ->color('danger')
                    ->icon('heroicon-o-user-minus')
                    ->visible(fn (Session $record): bool => auth()->user()?->can('markNoShow', $record)
                        && app(MentoringReportService::class)->canMarkNoShow($record))
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
                    }),
            ])
            ->emptyStateHeading('No pending mentoring reports in this training group');
    }

    /**
     * @return array<int, string>
     */
    private function getVisibleCtsPositions(): array
    {
        if (empty($this->category)) {
            return [];
        }

        return app(MentorPermissionService::class)->getAllCtsCallsignsForCategory($this->category);
    }

    public function render()
    {
        return view('livewire.training.pending-mentoring-reports-table');
    }
}
