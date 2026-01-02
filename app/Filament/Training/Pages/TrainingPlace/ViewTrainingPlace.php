<?php

namespace App\Filament\Training\Pages\TrainingPlace;

use App\Filament\Training\Pages\TrainingPlace\Widgets\TrainingPlaceStatsWidget;
use App\Models\Atc\Position;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ViewTrainingPlace extends Page implements HasInfolists, HasTable
{
    use InteractsWithInfolists;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.training.pages.view-training-place';

    protected static ?string $slug = 'training-places/{trainingPlaceId}';

    public TrainingPlace $trainingPlace;

    public string $trainingPlaceId;

    public function mount(): void
    {
        // Check training places view permission
        /** @var \App\Models\Mship\Account|null $user */
        $user = Auth::user();
        if (! $user || ! $user->can('training-places.view.*')) {
            abort(403, 'You do not have permission to view training places.');
        }

        $this->trainingPlace = TrainingPlace::where('id', $this->trainingPlaceId)->with('waitingListAccount', 'trainingPosition')->firstOrFail();
    }

    protected function getHeaderWidgets(): array
    {
        return [
            TrainingPlaceStatsWidget::make([
                'trainingPlace' => $this->trainingPlace,
            ]),
        ];
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        /** @var \App\Models\Mship\Account|null $user */
        $user = Auth::user();
        if ($user && $user->can('training.exams.setup')) {
            $actions[] = Action::make('forwardForExam')
                ->label('Forward for Practical Exam')
                ->icon('heroicon-o-arrow-right')
                ->disabled(fn () => $this->hasPendingExam())
                ->tooltip(fn () => $this->hasPendingExam() ? 'This member already has a pending exam booking.' : 'Forward the member for a practical exam on their primary training position')
                ->form([
                    Select::make('position_id')
                        ->label('Position')
                        ->options(fn () => Position::where('callsign', 'NOT LIKE', '%ATIS%')->orderBy('callsign')->pluck('callsign', 'id'))
                        ->default(fn () => $this->trainingPlace->trainingPosition->position->id)
                        ->required()
                        ->searchable()
                        ->preload(),
                    TextInput::make('student_name')
                        ->label('Student Name')
                        ->default(fn () => $this->trainingPlace->waitingListAccount->account->name)
                        ->disabled()
                        ->dehydrated(false),
                    TextInput::make('student_cid')
                        ->label('Student CID')
                        ->default(fn () => $this->trainingPlace->waitingListAccount->account->id)
                        ->disabled()
                        ->dehydrated(false),
                ])
                ->action(fn (array $data) => $this->forwardForExam($data['position_id']))
                ->modalHeading('Forward for Practical Exam')
                ->modalDescription('Confirm the details below to forward this member for a practical exam.')
                ->modalSubmitActionLabel('Forward for Exam');
        }

        return $actions;
    }

    private function hasPendingExam(): bool
    {
        return ExamBooking::where('student_id', $this->trainingPlace->waitingListAccount->account->member->id)
            ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
            ->exists();
    }

    public function forwardForExam(int $positionId): void
    {
        try {
            // Get the position from the provided ID
            $position = Position::findOrFail($positionId);
            $ctsMember = $this->trainingPlace->waitingListAccount->account->member;

            if (! $position || ! $ctsMember) {
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('Unable to forward for exam - missing position or member information.')
                    ->send();

                return;
            }

            // Check if the member has an ATC qualification
            if (! $ctsMember->account->qualification_atc) {
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('Unable to forward for exam - member does not have a valid ATC qualification.')
                    ->send();

                return;
            }

            /** @var \App\Models\Mship\Account|null $user */
            $user = Auth::user();
            if (! $user) {
                Notification::make()
                    ->title('Error')
                    ->danger()
                    ->body('Unable to determine current user.')
                    ->send();

                return;
            }

            // Create the exam setup record
            $setup = ExamSetup::create([
                'rts_id' => $position->rts,
                'student_id' => $ctsMember->id,
                'position_1' => $position->callsign,
                'position_2' => null,
                'exam' => $position->examLevel,
                'setup_by' => $user->id,
                'setup_date' => Carbon::now()->format('Y-m-d H:i:s'),
                'response' => 1,
                'dealt_by' => $user->id,
                'dealt_date' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            // Create the exam booking record
            $examBooking = ExamBooking::create([
                'rts_id' => $position->rts,
                'student_id' => $ctsMember->id,
                'student_rating' => $ctsMember->account->qualification_atc->vatsim,
                'position_1' => $position->callsign,
                'position_2' => null,
                'exam' => $position->examLevel,
            ]);

            // Link the exam setup to the booking
            $setup->update([
                'bookid' => $examBooking->id,
            ]);

            Notification::make()
                ->title('Success')
                ->success()
                ->body('Exam setup for '.$position->callsign.' has been created.')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->danger()
                ->body('An error occurred while forwarding for exam: '.$e->getMessage())
                ->send();
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {

        return $infolist->record($this->trainingPlace)->schema([
            Section::make('Training Place Details')->schema([
                TextEntry::make('waitingListAccount.account.name')->label('Name'),
                TextEntry::make('waitingListAccount.account.id')->label('CID'),
                TextEntry::make('trainingPosition.position.name')->label('Position'),
                TextEntry::make('created_at')->label('Training Start')->date('d/m/Y'),
                TextEntry::make('waitingListAccount.created_at')->label('Waiting List Join Date')->date('d/m/Y'),
            ])->columns(2),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Mentoring session history')
            ->queryStringIdentifier('mentoring')
            ->query(Session::query()->whereIn('position', $this->trainingPlace->trainingPosition->cts_positions)->where('student_id', $this->trainingPlace->waitingListAccount->account->member->id))
            ->defaultSort('taken_date', 'desc')
            ->paginated([10])
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('position')->label('Position'),
                TextColumn::make('taken_date')->label('Date')->date('d/m/Y'),
                TextColumn::make('mentor.cid')->label('Mentor CID'),
                TextColumn::make('mentor.name')->label('Mentor'),
                TextColumn::make('status')->label('Status')->badge()->getStateUsing(
                    fn ($record) => match (true) {
                        $record->noShow == 1 => 'No Show',
                        $record->cancelled_datetime != null => 'Cancelled',
                        $record->session_done == 1 => 'Completed',
                        default => 'Pending',
                    })
                    ->color(fn ($state) => match ($state) {
                        'Pending' => 'primary',
                        'No Show' => 'danger',
                        'Cancelled' => 'warning',
                        'Completed' => 'success',
                    }),
            ])
            ->actions([
                ViewAction::make()->url(fn ($record) => "https://cts.vatsim.uk/mentors/report.php?id={$record->id}&view=report"),
            ])
            ->emptyStateHeading('No mentoring sessions found');
    }
}
