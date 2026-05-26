<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Mentor\ConductMentoringSession;
use App\Models\Cts\Session;
use App\Services\Training\MentoringReportService;
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
            ->recordActions([
                Action::make('conduct')
                    ->label('Conduct')
                    ->url(fn (Session $record): string => ConductMentoringSession::getUrl(['sessionId' => $record->id]))
                    ->visible(fn (Session $record): bool => auth()->user()?->can('conduct', $record) ?? false),
                $this->markNoShowTableAction(),
            ])
            ->emptyStateHeading('No upcoming mentoring sessions found');
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

    public function render()
    {
        return view('livewire.training.accepted-mentoring-sessions-table');
    }
}
