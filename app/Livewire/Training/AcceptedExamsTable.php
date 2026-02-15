<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Exam\ConductExam;
use App\Models\Cts\ExamBooking;
use App\Services\Training\ExamAnnouncementService;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class AcceptedExamsTable extends Component implements HasForms, HasTable
{
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
            ->actions([
                Action::make('Conduct')
                    ->url(fn (ExamBooking $exam): string => ConductExam::getUrl(['examId' => $exam->id]))
                    ->visible(fn (ExamBooking $examBooking) => $examBooking->finished != ExamBooking::FINISHED_FLAG),
                Action::make('postExamAnnouncement')
                    ->label('Post Exam Announcement')
                    ->icon('heroicon-o-megaphone')
                    ->color('info')
                    ->visible(function (ExamBooking $examBooking): bool {
                        if ($examBooking->exam === 'OBS') {
                            return false;
                        }
                        // use CTS member ID rather than Core acocunt ID.
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
