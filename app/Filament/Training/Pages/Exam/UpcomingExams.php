<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Exam;

use App\Models\Cts\ExamBooking;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class UpcomingExams extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.training.pages.upcoming-exams';

    protected static ?int $navigationSort = 25;

    protected static string|\UnitEnum|null $navigationGroup = 'Exams';

    protected static ?string $title = 'Upcoming Exams';

    protected static ?string $navigationLabel = 'Upcoming Exams';

    protected static ?string $slug = 'exams/upcoming';

    protected const ATC_LEVELS = ['OBS', 'TWR', 'APP', 'CTR'];

    protected const PILOT_LEVELS = ['P1', 'P2', 'P3'];

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('training.exams.view-upcoming.*')
            || auth()->user()?->can('training.exams.view-upcoming.atc')
            || auth()->user()?->can('training.exams.view-upcoming.pilot');
    }

    public function mount(): void
    {
        if ($this->category === 'all') {
            if (! $this->hasMultipleVisibleCategories()) {
                $this->category = $this->firstVisibleCategory() ?? '';
            }

            return;
        }

        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->defaultCategory();
        }
    }

    protected function getHeaderActions(): array
    {
        $visibleCategories = $this->getVisibleCategories();

        $categoryActions = collect($visibleCategories)
            ->map(fn (string $cat) => Action::make('cat_'.str($cat)->slug('_'))
                ->label($cat === 'atc' ? 'ATC' : 'Pilot')
                ->url(static::getUrl(['category' => $cat]))
                ->icon($this->category === $cat ? 'heroicon-m-check' : null)
            );

        if ($this->hasMultipleVisibleCategories()) {
            $categoryActions = $categoryActions->prepend(
                Action::make('cat_all')
                    ->label('All')
                    ->url(static::getUrl(['category' => 'all']))
                    ->icon($this->category === 'all' ? 'heroicon-m-check' : null)
            );
        }

        return [
            ActionGroup::make($categoryActions->all())
                ->label('Training Department: '.$this->trainingDepartmentLabel())
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getUpcomingExamsQuery())
            ->defaultSort('taken_date', 'asc')
            ->defaultSort('taken_from', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->heading('Upcoming Exams')
            ->columns($this->getTableColumns())
            ->filters($this->getTableFilters())
            ->emptyStateHeading('No upcoming exams in this category');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('student_name')
                ->label('Student')
                ->getStateUsing(fn ($record) => $record->student?->name)
                ->description(fn ($record) => $record->student?->cid),

            TextColumn::make('exam')
                ->label('Level')
                ->badge()
                ->color('gray'),

            TextColumn::make('position_1')
                ->label('Position'),

            TextColumn::make('primary_examiner')
                ->label('Primary Examiner')
                ->getStateUsing(fn ($record) => $record->examiners?->primaryExaminer?->name)
                ->description(fn ($record) => $record->examiners?->primaryExaminer?->cid),

            TextColumn::make('start_date')
                ->label('Date & Time')
                ->getStateUsing(function ($record) {
                    $date = Carbon::parse($record->taken_date)->format('d/m/Y');
                    $time = Carbon::parse($record->taken_from)->format('H:i');

                    return trim("{$date} {$time}");
                })
                ->sortable(query: fn (Builder $query, string $direction) => $query
                    ->orderBy('taken_date', $direction)
                    ->orderBy('taken_from', $direction)
                ),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('exam')
                ->label('Level')
                ->options($this->getExamLevelOptions())
                ->multiple()
                ->searchable(),
        ];
    }

    protected function getUpcomingExamsQuery(): Builder
    {
        $query = ExamBooking::query()
            ->with(['student', 'examiners.primaryExaminer'])
            ->conductable()
            ->whereHas('examiners')
            ->where(function (Builder $query) {
                $query->whereDate('taken_date', '>', now()->toDateString())
                    ->orWhere(function (Builder $query) {
                        $query->whereDate('taken_date', now()->toDateString())
                            ->where('taken_from', '>', now()->toTimeString());
                    });
            });

        return $this->applyCategoryFilter($query);
    }

    protected function applyCategoryFilter(Builder $query): Builder
    {
        return match ($this->category) {
            'atc' => $query->whereIn('exam', self::ATC_LEVELS),
            'pilot' => $query->whereIn('exam', self::PILOT_LEVELS),
            default => $query,
        };
    }

    protected function getExamLevelOptions(): array
    {
        $levels = [];

        if ($this->canViewCategory('atc')) {
            foreach (self::ATC_LEVELS as $level) {
                $levels[$level] = $level;
            }
        }

        if ($this->canViewCategory('pilot')) {
            foreach (self::PILOT_LEVELS as $level) {
                $levels[$level] = $level;
            }
        }

        return $levels;
    }

    private function getVisibleCategories(): array
    {
        return collect(['atc', 'pilot'])
            ->filter(fn (string $cat) => $this->canViewCategory($cat))
            ->values()
            ->all();
    }

    private function hasMultipleVisibleCategories(): bool
    {
        return count($this->getVisibleCategories()) > 1;
    }

    private function canViewCategory(string $category): bool
    {
        if (auth()->user()?->can('training.exams.view-upcoming.*')) {
            return true;
        }

        return auth()->user()?->can('training.exams.view-upcoming.'.$category);
    }

    private function firstVisibleCategory(): ?string
    {
        return collect(['atc', 'pilot'])
            ->first(fn (string $cat) => $this->canViewCategory($cat));
    }

    private function defaultCategory(): string
    {
        if ($this->hasMultipleVisibleCategories()) {
            return 'all';
        }

        return $this->firstVisibleCategory() ?? '';
    }

    private function trainingDepartmentLabel(): string
    {
        if ($this->category === 'all') {
            return 'All';
        }

        return $this->category === 'atc' ? 'ATC' : 'Pilot';
    }
}
