<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Exam\ConductExam;
use App\Models\Cts\ExamBooking;
use App\Services\Training\CancelPendingExamService;
use App\Services\Training\ExamAnnouncementService;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Livewire\Component;

class AcceptedExamsTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    protected $listeners = ['exam-accepted' => '$refresh'];

    public function table(Table $table): Table
    {
        return $table
            ->heading('Accepted Exams')
            ->description('Exams that are currently accepted and you are assigned to conduct will be displayed here.')
            ->query(ExamBooking::query()
                ->with(['student', 'examiners'])
                ->conductable()
                ->whereHas('examiners', function ($query) {
                    $accountMemberId = auth()->user()->member->id;

                    return $query->where('senior', $accountMemberId)
                        ->orWhere('other', $accountMemberId)
                        ->orWhere('trainee', $accountMemberId);
                }))
            ->columns([
                TextColumn::make('student.cid')->label('CID'),
                TextColumn::make('student.name')->label('Name'),
                TextColumn::make('examiners.primaryExaminer.name')->label('Primary Examiner'),
                TextColumn::make('exam')->label('Level'),
                TextColumn::make('position_1')->label('Position'),
                TextColumn::make('start_date')->label('Date'),
            ])
            ->recordActions([
                Action::make('Conduct')
                    ->url(fn (ExamBooking $exam): string => ConductExam::getUrl(['examId' => $exam->id]))
                    ->visible(fn (ExamBooking $examBooking) => $examBooking->finished != ExamBooking::FINISHED_FLAG),
                Action::make('CancelExam')
                    ->label('Cancel exam')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(function (ExamBooking $examBooking): bool {
                        if ($examBooking->finished == ExamBooking::FINISHED_FLAG || ! $examBooking->exam) {
                            return false;
                        }

                        return auth()->user()->can('training.exams.conduct.'.Str::lower($examBooking->exam));
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Cancel exam')
                    ->modalDescription('This will release the exam slot, remove examiner assignments, and notify the student. Co-examiners can view the cancellation reason on the exam cancellations dashboard. This cannot be undone from here.')
                    ->modalSubmitActionLabel('Yes, cancel exam')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Reason for cancellation')
                            ->helperText('This will be sent to the student. Co-examiners can view it on the exam cancellations dashboard.')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (ExamBooking $record, array $data, CancelPendingExamService $service): void {
                        try {
                            $service->cancelByExaminer($record, strip_tags($data['reason']), auth()->user());
                        } catch (AuthorizationException $e) {
                            Notification::make()
                                ->title('Unable to cancel exam')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Exam cancelled')
                            ->body('The student has been notified. Co-examiners can view the reason on the exam cancellations dashboard.')
                            ->success()
                            ->send();

                        $this->dispatch('exam-accepted');
                    }),
                Action::make('postExamAnnouncement')
                    ->label('Post Exam Announcement')
                    ->icon('heroicon-o-megaphone')
                    ->color('info')
                    ->visible(function (ExamBooking $examBooking): bool {
                        if ($examBooking->exam === 'OBS') {
                            return false;
                        }
                        // use CTS member ID rather than Core account ID.
                        $memberId = auth()->user()->member->id;

                        return app(ExamAnnouncementService::class)->canPostAnnouncement($examBooking, $memberId);
                    })
                    ->form([
                        Checkbox::make('ping_exam_pilot')
                            ->label('Ping: Exam Pilot')
                            ->default(true),

                        Checkbox::make('ping_exam_controller')
                            ->label('Ping: Exam Controller')
                            ->default(false),

                        Textarea::make('notes')
                            ->label('Additional notes')
                            ->placeholder('Optional: additional notes')
                            ->rows(4)
                            ->maxLength(1000),

                    ])
                    ->requiresConfirmation()
                    ->action(function (ExamBooking $examBooking, array $data): void {
                        try {
                            app(ExamAnnouncementService::class)->postAnnouncement($examBooking, $data);

                            Notification::make()
                                ->title('Discord notification sent')
                                ->success()
                                ->send();

                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Failed to post to Discord')
                                ->danger()
                                ->send();
                        }

                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.training.accepted-exams-table');
    }
}
