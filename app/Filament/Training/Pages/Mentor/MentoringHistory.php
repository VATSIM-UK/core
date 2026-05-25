<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Filament\Training\Support\MentoringTrainingGroupBadgeColor;
use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Policies\Training\Mentoring\MentoringPolicy;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class MentoringHistory extends BaseMentoringHistoryPage
{
    use RemembersTrainingGroupCategory;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected string $view = 'filament.training.pages.mentoring-history';

    protected static ?int $navigationSort = 30;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $title = 'Mentoring History';

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('viewAny', Session::class) ?? false;
    }

    public function mount(): void
    {
        $this->rememberCategory();

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            if (! $this->hasMultipleVisibleCategories()) {
                $this->category = $this->firstVisibleCategory() ?? '';
            }

            return;
        }

        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->defaultCategory();
        }

        $this->saveCategoryToSession();
    }

    protected function getHeaderActions(): array
    {
        $categoryActions = collect($this->getVisibleCategories())
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

    protected function getPositionColumnBadgeColor(): string|array|\Closure
    {
        if ($this->category !== MentorPermissionService::ALL_CATEGORIES) {
            return 'gray';
        }

        return fn ($record) => MentoringTrainingGroupBadgeColor::forCtsCallsign($record->position);
    }

    protected function getSessionQuery(): Builder
    {
        return (new SessionRepository)
            ->getAllAcceptedSessionsForPositionsQuery($this->getVisibleCtsPositions())
            ->where('taken_date', '<', now());
    }

    protected function getPositionFilterOptions(): array
    {
        $positions = $this->getVisibleCtsPositions();

        return array_combine($positions, $positions);
    }

    private function getVisibleCtsPositions(): array
    {
        $user = auth()->user();
        $policy = app(MentoringPolicy::class);
        $scope = new MentoringScope;

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            if ($policy->viewAll($user)) {
                return app(MentorPermissionService::class)
                    ->getAllCtsCallsignsForCategories($this->getVisibleCategories());
            }

            return $user->getAllAssignedCallsigns();
        }

        if (empty($this->category)) {
            return [];
        }

        return $policy->visibleCtsPositionsForCategory($user, $scope, $this->category);
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
        return auth()->user()?->getAvailableMentoringCategories() ?? [];
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
        return auth()->user()?->can('viewCategory', [new MentoringScope, $category]) ?? false;
    }

    private function firstVisibleCategory(): ?string
    {
        return collect($this->getVisibleCategories())->first();
    }
}
