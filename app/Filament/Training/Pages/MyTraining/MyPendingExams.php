<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Models\Cts\Booking;
use App\Models\Cts\CancelReason;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\ExamSetup;
use App\Models\Cts\Reminder;
use App\Notifications\Training\Exams\ExamCancelledStudentNotification;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class MyPendingExams extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clock';

    protected string $view = 'filament.training.pages.my-training.my-pending-exams';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Pending Exams';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('training.access') ?? false;
    }

    public function table(Table $table): Table
    {
        $user = auth()->user();

        return $table
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
            ->actions([
                ActionGroup::make([
                    Action::make('cancelExamRequest')
                        ->label('Cancel Exam Request')
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->visible(fn (ExamBooking $record) => ! $record->taken)
                        ->requiresConfirmation()
                        ->modalHeading('Cancel Exam Request')
                        ->modalDescription('Are you sure you want to cancel this exam request? This action cannot be undone.')
                        ->form([
                            Textarea::make('reason')
                                ->label('Reason for cancellation')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function (ExamBooking $record, array $data): void {
                            $user = auth()->user();
                            $reason = strip_tags($data['reason']);

                            DB::connection('cts')->transaction(function () use ($record, $reason, $user) {
                                CancelReason::create([
                                    'sesh_id' => $record->id,
                                    'sesh_type' => 'EX',
                                    'reason' => $reason,
                                    'reason_by' => $user->id,
                                ]);

                                $record->setup()->update(['booked' => 0]);

                                Booking::where('type_id', $record->id)->where('type', 'EX')->delete();
                                Reminder::where('sesh_id', $record->id)->where('sesh_type', 'E')->delete();

                                $user->notify(new ExamCancelledStudentNotification($record, $reason));
                                $record->delete();
                            });

                            Notification::make()
                                ->title('Exam request cancelled')
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->paginated(false)
            ->emptyStateHeading('No pending exam requests')
            ->emptyStateDescription('You have no pending exam requests.');
    }
}
