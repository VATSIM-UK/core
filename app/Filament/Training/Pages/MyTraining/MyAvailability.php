<?php

namespace App\Filament\Training\Pages\MyTraining;

use App\Filament\Training\Pages\MyTraining\Widgets\MyAvailabilityStats;
use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction as ActionsDeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TimePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyAvailability extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.training.pages.my-training.my-availability';

    protected static string|\UnitEnum|null $navigationGroup = 'My Training';

    protected static ?string $navigationLabel = 'My Availability';

    protected static ?int $navigationSort = 15;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('training.access') ?? false;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyAvailabilityStats::class,
        ];
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
                    ->label('Time (Zulu)')
                    ->state(fn (Availability $record): string => $record->from->format('H:i').' - '.$record->to->format('H:i')),
                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(function (Availability $record): string {
                        $start = Carbon::parse($record->date->format('Y-m-d').' '.$record->from->format('H:i:s'));
                        $end = Carbon::parse($record->date->format('Y-m-d').' '.$record->to->format('H:i:s'));
                        $minutes = max(0, $start->diffInMinutes($end));
                        $hours = intdiv($minutes, 60);
                        $remainingMinutes = $minutes % 60;

                        if ($hours === 0) {
                            return "{$remainingMinutes}m";
                        }

                        if ($remainingMinutes === 0) {
                            return "{$hours}h";
                        }

                        return "{$hours}h {$remainingMinutes}m";
                    }),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Availability')
                    ->icon('heroicon-o-plus')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['student_id'] = $this->resolveStudentId();
                        $data['type'] = 'S';

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->form($this->getFormSchema())
                    ->mutateRecordDataUsing(function (array $data): array {
                        $data['from'] = Carbon::parse($data['from'])->format('H:i');
                        $data['to'] = Carbon::parse($data['to'])->format('H:i');

                        return $data;
                    }),
                DeleteAction::make(),
            ])
            ->bulkActions([
                ActionsDeleteBulkAction::make(),
            ])
            ->emptyStateHeading('No availability added yet')
            ->emptyStateDescription('Add your available training slots so mentors can schedule sessions around your real availability.')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('date')
                ->label('Date')
                ->required()
                ->native(false)
                ->minDate(now()->startOfDay()),
            TimePicker::make('from')
                ->label('From (UTC)')
                ->required()
                ->seconds(false)
                ->native(false),
            TimePicker::make('to')
                ->label('To (UTC)')
                ->required()
                ->seconds(false)
                ->native(false)
                ->after('from'),
            Hidden::make('type')
                ->default('S'),
        ];
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
