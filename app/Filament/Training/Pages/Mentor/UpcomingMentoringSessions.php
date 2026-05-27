<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class UpcomingMentoringSessions extends BaseMentoringHistoryPage
{
    use RemembersTrainingGroupCategory;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected string $view = 'filament.training.pages.upcoming-mentoring-sessions';

    protected static ?int $navigationSort = 25;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $title = 'Upcoming Sessions';

    protected static ?string $navigationLabel = 'Upcoming Sessions';

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
        if (! app()->runningUnitTests() && ! auth()->user()?->can('training.beta')) {
            return false;
        }

        if (auth()->user()?->can('training.mentoring.view.*')) {
            return true;
        }

        return auth()->user()?->can('training.mentors.view.atc')
            || auth()->user()?->can('training.mentors.view.pilot');
    }

    public function mount(): void
    {
        $this->rememberCategory();

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            if (! $this->hasMultipleVisibleCategories()) {
                $this->category = $this->firstVisibleCategory() ?? '';
            }

            $this->saveCategoryToSession();

            return;
        }

        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->defaultCategory();
        }

        $this->saveCategoryToSession();
    }

    protected function getHeaderActions(): array
    {
        $visibleCategories = $this->getVisibleCategories();

        $categoryActions = collect($visibleCategories)
            ->map(fn (string $cat) => Action::make('cat_'.str($cat)->slug('_'))
                ->label($cat)
                ->url(static::getUrl(['category' => $cat]))
                ->icon($this->category === $cat ? 'heroicon-m-check' : null)
            );

        if ($this->hasMultipleVisibleCategories()) {
            $categoryActions = $categoryActions->prepend(
                Action::make('cat_all')
                    ->label('All')
                    ->url(static::getUrl(['category' => MentorPermissionService::ALL_CATEGORIES]))
                    ->icon($this->category === MentorPermissionService::ALL_CATEGORIES ? 'heroicon-m-check' : null)
            );
        }

        return [
            ActionGroup::make($categoryActions->all())
                ->label('Training Group: '.$this->trainingGroupLabel())
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
        ];
    }

    public function table(Table $table): Table
    {
        return parent::table($table)
            ->heading('Upcoming Sessions')
            ->description('Accepted mentoring sessions scheduled for the future.');
    }

    protected function getSessionQuery(): Builder
    {
        return (new SessionRepository)
            ->getUpcomingAcceptedSessionsForPositionsQuery($this->getVisibleCtsPositions());
    }

    protected function getPositionFilterOptions(): array
    {
        $positions = $this->getVisibleCtsPositions();

        return array_combine($positions, $positions);
    }

    protected function defaultTableSortDirection(): string
    {
        return 'asc';
    }

    protected function tableRecordActions(): array
    {
        return [];
    }

    protected function tableEmptyStateHeading(): string
    {
        return 'No upcoming mentoring sessions in this training group';
    }

    protected function includeStatusColumn(): bool
    {
        return false;
    }

    protected function includeStatusFilter(): bool
    {
        return false;
    }

    private function getVisibleCtsPositions(): array
    {
        $service = app(MentorPermissionService::class);

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            return $service->getAllCtsCallsignsForCategories($this->getVisibleCategories());
        }

        if (empty($this->category)) {
            return [];
        }

        return $service->getAllCtsCallsignsForCategory($this->category);
    }

    private function trainingGroupLabel(): string
    {
        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            return 'All';
        }

        return $this->category;
    }

    /**
     * @return array<int, string>
     */
    private function getVisibleCategories(): array
    {
        return collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories())
            ->filter(fn (string $cat) => $this->canViewCategory($cat))
            ->values()
            ->all();
    }

    private function hasMultipleVisibleCategories(): bool
    {
        return count($this->getVisibleCategories()) > 1;
    }

    private function defaultCategory(): string
    {
        if ($this->hasMultipleVisibleCategories()) {
            return MentorPermissionService::ALL_CATEGORIES;
        }

        return $this->firstVisibleCategory() ?? '';
    }

    private function canViewCategory(string $category): bool
    {
        if (auth()->user()->can('training.mentoring.view.*')) {
            return true;
        }

        return auth()->user()->can('training.mentors.view.'.MentorPermissionService::categoryType($category));
    }

    private function firstVisibleCategory(): ?string
    {
        return collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories())
            ->first(fn (string $cat) => $this->canViewCategory($cat));
    }
}
