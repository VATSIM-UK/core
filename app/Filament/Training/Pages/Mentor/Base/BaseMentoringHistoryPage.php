<?php

namespace App\Filament\Training\Pages\Mentor\Base;

use App\Filament\Training\Pages\Mentor\ViewMentoringReport;
use App\Filament\Training\Support\TrainingMemberAccountSearch;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseMentoringHistoryPage extends Page implements HasTable
{
    use InteractsWithTable;

    abstract protected function getSessionQuery(): Builder;

    protected function getPositionFilterOptions(): array
    {
        return [];
    }

    protected function showStudentFilter(): bool
    {
        return true;
    }

    protected function showMentorFilter(): bool
    {
        return true;
    }

    protected function defaultTableSortDirection(): string
    {
        return 'desc';
    }

    protected function tableRecordActions(): array
    {
        return [
            Action::make('view')
                ->label('View Report')
                ->url(fn ($record) => ViewMentoringReport::getUrl(['sessionId' => $record->id]))
                ->visible(fn ($record) => $record->filed !== null)
                ->openUrlInNewTab(),
        ];
    }

    protected function tableEmptyStateHeading(): string
    {
        return 'No mentoring sessions found in this training group';
    }

    protected function includeStatusColumn(): bool
    {
        return true;
    }

    protected function includeStatusFilter(): bool
    {
        return true;
    }

    /**
     * @return string|array<int, string>|\Closure(mixed, mixed): string|array<int, string>
     */
    protected function getPositionColumnBadgeColor(): string|array|\Closure
    {
        return 'gray';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getSessionQuery())
            ->defaultSort('taken_date', $this->defaultTableSortDirection())
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->recordActions($this->tableRecordActions())
            ->emptyStateHeading($this->tableEmptyStateHeading());
    }

    protected function getTableColumns(): array
    {
        $columns = [];

        if ($this->showStudentFilter()) {
            $columns[] = TextColumn::make('student_name')
                ->label('Student')
                ->getStateUsing(fn ($record) => $record->student->name)
                ->description(fn ($record) => $record->student->cid)
                ->action(function ($record, $livewire) {
                    $livewire->tableFilters['student']['value'] = $record->student->cid;
                    $livewire->updatedTableFilters();
                });
        }

        $columns[] = TextColumn::make('mentor_name')
            ->label('Mentor')
            ->getStateUsing(fn ($record) => $record->mentor->name)
            ->description(fn ($record) => $record->mentor->cid)
            ->action(function ($record, $livewire) {
                if (! $this->showMentorFilter()) {
                    return;
                }

                $livewire->tableFilters['mentor']['value'] = $record->mentor->cid;
                $livewire->updatedTableFilters();
            });

        $columns[] = TextColumn::make('position')
            ->label('Position')
            ->badge()
            ->color($this->getPositionColumnBadgeColor());

        $columns[] = TextColumn::make('taken_date')
            ->label('Date & Time')
            ->getStateUsing(function ($record) {
                $date = Carbon::parse($record->taken_date)->format('d/m/Y');
                $time = Carbon::parse($record->taken_from)->format('H:i');

                return trim("{$date} {$time}");
            })
            ->sortable(query: fn (Builder $query, string $direction) => $query
                ->orderBy('taken_date', $direction)
                ->orderBy('taken_from', $direction)
            );

        if ($this->includeStatusColumn()) {
            $columns[] = TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->getStateUsing(fn ($record) => match (true) {
                    $record->noShow == 1 => 'No Show',
                    $record->cancelled_datetime !== null => 'Cancelled',
                    $record->filed !== null => 'Completed',
                    default => 'Pending',
                })
                ->color(fn ($state) => match ($state) {
                    'Pending' => 'primary',
                    'No Show' => 'danger',
                    'Cancelled' => 'warning',
                    'Completed' => 'success',
                });
        }

        return $columns;
    }

    private function getTableFilters(): array
    {
        $filters = [];

        if (! empty($this->getPositionFilterOptions())) {
            $filters[] = SelectFilter::make('position')
                ->label('Position')
                ->options($this->getPositionFilterOptions())
                ->searchable()
                ->multiple();
        }

        if ($this->showStudentFilter()) {
            $filters[] = SelectFilter::make('student')
                ->label('Student')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search) => TrainingMemberAccountSearch::searchAccountsForSelect($search))
                ->getOptionLabelUsing(fn ($value) => Member::where('cid', $value)->first()?->name." ({$value})")
                ->query(fn (Builder $query, array $data): Builder => $query->when(
                    $data['value'],
                    fn ($q, $value) => $q->whereHas('student', fn ($q) => $q->where('cid', $value))
                ));
        }

        if ($this->showMentorFilter()) {
            $filters[] = SelectFilter::make('mentor')
                ->label('Mentor')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search) => TrainingMemberAccountSearch::searchAccountsForSelect($search))
                ->getOptionLabelUsing(fn ($value) => Member::where('cid', $value)->first()?->name." ({$value})")
                ->query(fn (Builder $query, array $data): Builder => $query->when(
                    $data['value'],
                    fn ($q, $value) => $q->whereHas('mentor', fn ($q) => $q->where('cid', $value))
                ));
        }

        if ($this->includeStatusFilter()) {
            $filters[] = $this->statusFilter();
        }

        $filters[] = $this->dateRangeFilter();

        return $filters;
    }

    private function statusFilter(): SelectFilter
    {
        return SelectFilter::make('status')
            ->label('Status')
            ->multiple()
            ->options([
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'no_show' => 'No Show',
                'pending' => 'Pending',
            ])
            ->query(function (Builder $query, array $data): Builder {
                $values = array_filter($data['values'] ?? []);

                if (empty($values)) {
                    return $query;
                }

                return $query->where(function (Builder $q) use ($values): void {
                    foreach ($values as $value) {
                        $q->orWhere(function (Builder $inner) use ($value): void {
                            match ($value) {
                                'completed' => $inner->whereNotNull('filed'),
                                'cancelled' => $inner->whereNotNull('cancelled_datetime'),
                                'no_show' => $inner->where('noShow', 1),
                                'pending' => $inner->whereNull('filed')
                                    ->whereNull('cancelled_datetime')
                                    ->where('noShow', 0),
                            };
                        });
                    }
                });
            });
    }

    private function dateRangeFilter(): Filter
    {
        return Filter::make('taken_date')
            ->form([
                DatePicker::make('from')->label('From Date'),
                DatePicker::make('until')->label('To Date'),
            ])
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'], fn ($q, $date) => $q->whereDate('taken_date', '>=', $date))
                ->when($data['until'], fn ($q, $date) => $q->whereDate('taken_date', '<=', $date))
            );
    }
}
