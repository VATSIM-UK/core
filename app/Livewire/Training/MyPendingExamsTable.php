<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Concerns\AddToCalendar;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Services\Training\CancelPendingExamService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Spatie\CalendarLinks\Link;

class MyPendingExamsTable extends Component implements HasActions, HasForms, HasTable
{
    use AddToCalendar;
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
            ->heading('Pending Exams')
            ->query(
                ExamBooking::query()
                    ->select('exam_book.*')
                    ->selectSub(
                        ExamSetup::select('setup_date')
                            ->whereColumn('bookid', 'exam_book.id')
                            ->limit(1),
                        'setup_date'
                    )
                    ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
                    ->whereHas('student', fn ($query) => $query->where('cid', $user->id))
                    ->with(['student'])
                    ->orderByDesc('setup_date')
            )
            ->columns([
                TextColumn::make('exam')
                    ->label('Exam'),

                TextColumn::make('position_1')
                    ->label('Position'),

                TextColumn::make('taken_date')
                    ->label('Exam Date')
                    ->state(fn ($record) => $record->taken_date)
                    ->date()
                    ->placeholder('Not yet scheduled'),

                TextColumn::make('taken_time')
                    ->label('Exam Time')
                    ->state(function ($record): ?string {
                        if (! $record->taken) {
                            return null;
                        }

                        return Carbon::parse($record->start_date)->format('H:i').'Z – '.Carbon::parse($record->end_date)->format('H:i').'Z';
                    })
                    ->placeholder('Not yet scheduled'),
            ])
            ->recordActions([
                $this->getCalendarActionGroup()
                    ->visible(fn (ExamBooking $record) => $record->taken),
                ActionGroup::make([
                    Action::make('cancelExamRequest')
                        ->label('Cancel Exam')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->visible(fn (ExamBooking $record) => $record->taken)
                        ->requiresConfirmation()
                        ->modalHeading(fn (ExamBooking $record) => "Cancel {$record->exam} Exam")
                        ->modalDescription(fn (ExamBooking $record) => implode(' ', [
                            'You are about to cancel your', $record->exam, 'exam scheduled for', Carbon::parse($record->taken_date)->format('l jS M Y'), 'at', Carbon::parse($record->taken_from)->format('H:i').'Z –', Carbon::parse($record->taken_to)->format('H:i').'Z.', 'Your examiner will be notified.']))
                        ->form([
                            Textarea::make('reason')
                                ->label('Reason for cancellation')
                                ->helperText('This will be sent to your examiner.')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function (ExamBooking $record, array $data, CancelPendingExamService $service): void {
                            $service->cancelByStudent($record, strip_tags($data['reason']), auth()->user());

                            Notification::make()
                                ->title('Exam cancelled')
                                ->body('Your examiner has been notified.')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->paginated(false)
            ->emptyStateHeading('No pending exam requests')
            ->emptyStateDescription('You have no pending exam requests.');
    }

    protected function buildCalendarLinkObject(mixed $record): Link
    {
        \assert($record instanceof ExamBooking);

        return $this->buildExamBookingLink(
            $record,
            "Practical Exam - {$record->exam}",
            $record->position_1,
            "Exam Type: {$record->exam}\nPosition: {$record->position_1}"
        );
    }

    protected function getCalendarIcsFilename(mixed $record): string
    {
        \assert($record instanceof ExamBooking);

        return 'practical-exam-'.str($record->exam)->slug();
    }

    public function render()
    {
        return view('livewire.training.my-pending-exams-table');
    }
}
