<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
use Illuminate\Database\Eloquent\Builder;

class MyAvailability extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.training.pages.my-training.my-availability';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Availability';

    protected static ?int $navigationSort = 15;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->can('training.access') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'from' => '18:00',
            'to' => '21:00',
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyAvailabilityStats::class,
        ];
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('start_date')
                    ->label('Start Date')
                    ->required()
                    ->native(false)
                    ->minDate(now()->startOfDay())
                    ->default(today()),

                DatePicker::make('end_date')
                    ->label('End Date')
                    ->helperText('Leave blank for a single day, or set a date to add the same slot for every day in the range.')
                    ->native(false)
                    ->minDate(now()->startOfDay())
                    ->afterOrEqual('start_date'),

                Select::make('from')
                    ->label('From (UTC)')
                    ->required()
                    ->searchable()
                    ->allowHtml(false)
                    ->options($this->generateTimeOptions()),

                Select::make('to')
                    ->label('To (UTC)')
                    ->required()
                    ->searchable()
                    ->allowHtml(false)
                    ->options($this->generateTimeOptions()),
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

        $from = $data['from'];
        $to = $data['to'];

        if ($from >= $to) {
            Notification::make()
                ->title('Invalid time range')
                ->body('The end time must be after the start time.')
                ->danger()
                ->send();

            return;
        }

        $start = Carbon::parse($data['start_date']);
        $end = $data['end_date'] ? Carbon::parse($data['end_date']) : $start->copy();

        $createdCount = 0;
        $skippedCount = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            $date = $current->toDateString();
            $slotStart = Carbon::parse("{$date} {$from}");

            if ($slotStart->lessThanOrEqualTo(now())) {
                $skippedCount++;
                $current->addDay();

                continue;
            }

            $availability = Availability::query()->firstOrCreate([
                'student_id' => $studentId,
                'type' => 'S',
                'date' => $date,
                'from' => $from,
                'to' => $to,
            ]);

            $availability->wasRecentlyCreated ? $createdCount++ : $skippedCount++;

            $current->addDay();
        }

        if ($createdCount > 0) {
            Notification::make()
                ->title('Availability added')
                ->body("Created {$createdCount} slot(s).".($skippedCount > 0 ? " Skipped {$skippedCount} duplicate/past slot(s)." : ''))
                ->success()
                ->send();

            return;
        }

        Notification::make()
            ->title('No availability created')
            ->body('All selected dates were duplicates or in the past.')
            ->warning()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getAvailabilityQuery())
            ->defaultSort('date')
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('D j M Y'),

                TextColumn::make('time_window')
                    ->label('Time (UTC)')
                    ->state(fn (Availability $record): string => $record->from->format('H:i').' – '.$record->to->format('H:i')),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(function (Availability $record): string {
                        $start = Carbon::parse($record->date->format('Y-m-d').' '.$record->from->format('H:i:s'));
                        $end = Carbon::parse($record->date->format('Y-m-d').' '.$record->to->format('H:i:s'));
                        $minutes = max(0, $start->diffInMinutes($end));
                        $hours = intdiv($minutes, 60);
                        $rem = $minutes % 60;

                        if ($hours === 0) {
                            return "{$rem}m";
                        }

                        if ($rem === 0) {
                            return "{$hours}h";
                        }

                        return "{$hours}h {$rem}m";
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No availability added yet')
            ->emptyStateDescription('Use the form to add your available training slots.')
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

    protected function getAvailabilityQuery(): Builder
    {
        $studentId = $this->resolveStudentId();

        if (! $studentId) {
            return Availability::query()->whereRaw('1 = 0');
        }

        return Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->where(function (Builder $query): void {
                $today = now()->toDateString();
                $timeNow = now()->format('H:i:s');

                $query->whereDate('date', '>', $today)
                    ->orWhere(function (Builder $query) use ($today, $timeNow): void {
                        $query->whereDate('date', '=', $today)
                            ->whereTime('from', '>', $timeNow);
                    });
            });
    }

    protected function resolveStudentId(): ?int
    {
        $cid = auth()->id();

        if (! $cid) {
            return null;
        }

        return Member::query()
            ->where('cid', $cid)
            ->value('id');
    }
}
