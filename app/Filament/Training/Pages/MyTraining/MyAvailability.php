<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use CodeWithKyrian\FilamentDateRange\Forms\Components\DateRangePicker;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
class MyAvailability extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.training.pages.my-training.my-availability';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Availability';

    protected static ?string $slug = 'my-training/availability';

    protected static ?int $navigationSort = 5;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user?->can('training.access')) {
            return false;
        }

        $hasTrainingPlace = TrainingPlace::where('account_id', $user->id)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasTrainingPlace) {
            return true;
        }

        // Interm check for pilot position validations
        $ctsMember = Member::where('cid', $user->id)->first();

        if (! $ctsMember) {
            return false;
        }

        $pilotCallsigns = [
            'P1_PPL(A)',
            'P2_SEIR(A)',
            'P3_CMEL(A)',
            'P1_PPL(A)_MEN',
            'P2_SEIR(A)_MEN',
            'P3_CMEL(A)_MEN',
            'TFP_FLIGHT',
        ];

        $hasPilotValidation = PositionValidation::where('member_id', $ctsMember->id)
            ->whereHas('position', function ($query) use ($pilotCallsigns) {
                $query->whereIn('callsign', $pilotCallsigns);
            })
            ->exists();

        if ($hasPilotValidation) {
            return true;
        }

        return false;
    }

    protected function getAvailabilityService(): AvailabilityService
    {
        return app(AvailabilityService::class);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyAvailabilityStats::class,
        ];
    }

    public function mount(): void
    {
        $this->form->fill([
            'from' => '18:00',
            'to' => '21:00',
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DateRangePicker::make('date_range')
                    ->label('Date(s)')
                    ->required()
                    ->minDate(now()->startOfDay()),

                Select::make('from')
                    ->label('From')
                    ->required()
                    ->searchable()
                    ->allowHtml(false)
                    ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                    ->options($this->generateTimeOptions())
                    ->optionsLimit(100),

                Select::make('to')
                    ->label('To')
                    ->required()
                    ->searchable()
                    ->allowHtml(false)
                    ->searchPrompt('Type a time (e.g. 18:30) to filter the list')
                    ->options($this->generateTimeOptions())
                    ->optionsLimit(100),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return;
        }

        $startDate = Carbon::parse($data['date_range']['start']);
        $endDate = Carbon::parse($data['date_range']['end']);

        $addedCount = 0;
        $mergedCount = 0;

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            $dateString = $date->toDateString();

            $startUtc = Carbon::parse("{$dateString} {$data['from']}", 'UTC');
            $endUtc = Carbon::parse("{$dateString} {$data['to']}", 'UTC');

            $result = $this->getAvailabilityService()->addOrMergeSlot($studentId, $startUtc, $endUtc);

            match ($result) {
                'added' => $addedCount++,
                'merged' => $mergedCount++,
            };
        }

        if ($addedCount > 0 && $mergedCount > 0) {
            Notification::make()->title("Added {$addedCount} slot(s) and expanded {$mergedCount} existing slot(s)")->warning()->send();
        } elseif ($mergedCount > 0) {
            Notification::make()->title("Expanded {$mergedCount} existing slot(s) to cover the new time(s)")->warning()->send();
        } else {
            Notification::make()->title("{$addedCount} availability slot(s) added")->success()->send();
        }

        $this->form->fill([
            'from' => '18:00',
            'to' => '21:00',
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getAvailabilityService()->getFutureAvailabilityQuery(
                $this->getAvailabilityService()->resolveMemberId(auth()->id()) ?? 0
            ))
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->state(function (Availability $record) {
                        return Carbon::parse($record->date->format('Y-m-d').' '.$record->from->format('H:i:s'), 'UTC')
                            ->format('D j M Y');
                    }),

                TextColumn::make('time_window')
                    ->label('Time')
                    ->state(function (Availability $record): string {
                        $start = Carbon::parse($record->date->format('Y-m-d').' '.$record->from->format('H:i:s'), 'UTC');
                        $end = Carbon::parse($record->date->format('Y-m-d').' '.$record->to->format('H:i:s'), 'UTC');

                        if ($record->to->format('H:i:s') < $record->from->format('H:i:s')) {
                            $end->addDay();
                        }

                        return $start->format('H:i').' - '.$end->format('H:i');
                    }),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(fn (Availability $record) => $this->getAvailabilityService()->formatDuration(
                        $record->date, $record->from, $record->to
                    )),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->modalWidth('md')
                    ->successNotification(null)
                    ->modalSubmitActionLabel('Update')
                    ->mutateRecordDataUsing(function (Availability $record): array {
                        $start = Carbon::parse($record->date->format('Y-m-d').' '.$record->from->format('H:i:s'), 'UTC');
                        $end = Carbon::parse($record->date->format('Y-m-d').' '.$record->to->format('H:i:s'), 'UTC');

                        if ($record->to->format('H:i:s') < $record->from->format('H:i:s')) {
                            $end->addDay();
                        }

                        return [
                            'date' => $start->toDateString(),
                            'from' => $start->format('H:i'),
                            'to' => $end->format('H:i'),
                        ];
                    })
                    ->form([
                        DatePicker::make('date')->required()->native(false),
                        Select::make('from')->options($this->generateTimeOptions())->required(),
                        Select::make('to')->options($this->generateTimeOptions())->required(),
                    ])
                    ->action(function (Availability $record, array $data): void {
                        $startUtc = Carbon::parse("{$data['date']} {$data['from']}", 'UTC');
                        $endUtc = Carbon::parse("{$data['date']} {$data['to']}", 'UTC');

                        [$valid, $message] = $this->getAvailabilityService()->isSlotValid(
                            $record->student_id, $startUtc, $endUtc, $record->id
                        );

                        if (! $valid) {
                            Notification::make()->title($message)->danger()->send();

                            return;
                        }

                        $record->update([
                            'date' => $startUtc->toDateString(),
                            'from' => $startUtc->format('H:i:s'),
                            'to' => $endUtc->format('H:i:s'),
                        ]);

                        Notification::make()->title('Availability updated')->success()->send();
                    }),
                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->iconButton()
                    ->requiresConfirmation(false)
                    ->action(fn ($record) => $record->delete()),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No availability added yet')
            ->emptyStateDescription('Use the form to add your availability slots.')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(25);
    }

    protected function generateTimeOptions(): array
    {
        $options = [];

        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += 15) {
                $time = sprintf('%02d:%02d', $h, $m);
                $options[$time] = $time;
            }
        }

        return $options;
    }

    protected function resolveStudentId(): ?int
    {
        return $this->getAvailabilityService()->resolveMemberId(auth()->id());
    }
}
