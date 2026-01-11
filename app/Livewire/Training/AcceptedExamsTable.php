<?php

namespace App\Livewire\Training;

use App\Filament\Training\Pages\Exam\ConductExam;
use App\Models\Cts\ExamBooking;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;

use App\Libraries\Discord;

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
                    ->label("Post Exam Announcement")
                    ->icon('heroicon-o-megaphone')
                    ->color('info')
                    ->visible(function (ExamBooking $examBooking): bool {
                        if ($examBooking->finished == ExamBooking::FINISHED_FLAG) {
                            return false;
                        }

                        $memberId = auth()->user()->member->id;

                        $examiners = $examBooking->examiners;

                        return $examiners->senior === $memberId || $examiners->other === $memberId || $examiners->trainee === $memberId;
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
                        $channelId = config('training.discord.exam_announce_channel_id');

                        $startUtc = CarbonImmutable::parse($examBooking->start_date)->utc();
                        $unix = $startUtc->getTimestamp();

                        $position = $examBooking->position_1;
                        $level = $examBooking->exam;

                        $pilotRoleId = config('training.discord.exam_pilot_role_id');
                        $controllerRoleId = config('training.discord.exam_controller_role_id');

                        $mentions = collect([
                            !empty($data['ping_exam_pilot']) && filled($pilotRoleId) ? "<@&{$pilotRoleId}>" : null,
                            !empty($data['ping_exam_controller']) && filled($controllerRoleId) ? "<@&{$controllerRoleId}>" : null,
                        ])->filter()->implode(' ');


                        $notes = trim((string)($data['notes'] ?? ''));
                        $notesBlock = $notes !== '' ? "\n\n**Notes:**\n{$notes}" : '';
                        
                        $message =
                            ($mentions ? $mentions . "\n" : '') .
                            "**Upcoming {$level} Exam**\n" .
                            "There will be an exam on **{$position}** on **<t:{$unix}:F>** (<t:{$unix}:R>)" .
                            $notesBlock;

                        dd($message);
                        try {
                            $discord = new Discord();

                            $discord->sendMessageToChannel($channelId, [
                                'content' => $message,
                            ]);

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
