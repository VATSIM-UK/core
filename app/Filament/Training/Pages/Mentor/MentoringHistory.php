<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Filament\Training\Support\MentoringTrainingGroupBadgeColor;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Policies\Training\Mentoring\MentoringPolicy;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Url;

class MentoringHistory extends BaseMentoringHistoryPage
{
    use RemembersTrainingGroupCategory;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected string $view = 'filament.training.pages.mentoring-history';

    protected static ?int $navigationSort = 30;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $title = 'Mentoring History';

    protected static ?string $slug = 'mentoring/history';

    #[Url]
    public string $category = '';

    #[Url]
    public ?array $tableFilters = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->can('viewAny', Session::class)) {
            return true;
        }

        // mentors who have past mentoring sessions
        return (new SessionRepository)
            ->getSessionsForMentor($user->id)
            ->exists();
    }

    public function mount(): void
    {
        Log::debug('[MentoringHistory] mount() START', [
            'auth_id' => auth()->id(),
            'category_before' => $this->category ?: '(empty)',
            'saved_category_in_session' => session('training.mentoring.last_category'),
            'visible_categories' => $this->getVisibleCategories(),
            'has_multiple_visible' => $this->hasMultipleVisibleCategories(),
        ]);

        $this->rememberCategory();

        Log::debug('[MentoringHistory] mount() after rememberCategory', [
            'category_after_remember' => $this->category ?: '(empty)',
        ]);

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            Log::debug('[MentoringHistory] mount() ALL_CATEGORIES branch entered');

            if (! $this->hasMultipleVisibleCategories()) {
                $newCat = $this->firstVisibleCategory() ?? '';
                Log::debug('[MentoringHistory] mount() downgrading ALL to single category', [
                    'new_category' => $newCat ?: '(empty)',
                ]);
                $this->category = $newCat;
            }

            return;
        }

        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $newCat = $this->defaultCategory();
            Log::debug('[MentoringHistory] mount() resetting invalid/empty category', [
                'old_category' => $this->category ?: '(empty)',
                'can_view' => empty($this->category) ? 'n/a (empty)' : ($this->canViewCategory($this->category) ? 'yes' : 'no'),
                'new_category' => $newCat ?: '(empty)',
            ]);
            $this->category = $newCat;
        }

        Log::debug('[MentoringHistory] mount() END', [
            'final_category' => $this->category ?: '(empty)',
        ]);

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
        $sessionRepository = new SessionRepository;

        $member = Member::where('cid', auth()->id())->first();

        $visiblePositions = $this->getVisibleCtsPositions();

        Log::debug('[MentoringHistory] getSessionQuery START', [
            'auth_id' => auth()->id(),
            'category' => $this->category,
            'visible_categories' => $this->getVisibleCategories(),
            'visible_positions_count' => count($visiblePositions),
            'visible_positions_sample' => array_slice($visiblePositions, 0, 20),
            'member_exists' => $member !== null,
            'member_id' => $member?->id,
            'member_cid' => $member?->cid,
        ]);

        $sessionsWithPermissions = $sessionRepository
            ->getAllAcceptedSessionsForPositionsQuery($visiblePositions)
            ->where('taken_date', '<', now());

        $sessionsUserMentored = $sessionRepository
            ->getSessionsForMentor($member->cid);

        Log::debug('[MentoringHistory] Sub-queries built', [
            'permissions_sql' => $sessionsWithPermissions->toSql(),
            'permissions_bindings' => $sessionsWithPermissions->getBindings(),
            'mentored_sql' => $sessionsUserMentored->toSql(),
            'mentored_bindings' => $sessionsUserMentored->getBindings(),
        ]);

        // Run the mentor sub-query standalone to verify it returns expected rows
        $mentoredCount = (clone $sessionsUserMentored)->count();
        $mentoredSample = (clone $sessionsUserMentored)->limit(5)->pluck('position', 'id')->all();

        Log::debug('[MentoringHistory] Standalone mentor query results', [
            'mentored_count' => $mentoredCount,
            'mentored_sample' => $mentoredSample,
        ]);

        $union = $sessionsWithPermissions->union($sessionsUserMentored);

        Log::debug('[MentoringHistory] Union built', [
            'union_sql' => $union->toSql(),
            'union_bindings' => $union->getBindings(),
        ]);

        $finalQuery = Session::query()
            ->fromSub($union, 'sessions')
            ->orderByDesc('taken_date')
            ->orderByDesc('taken_from')
            ->orderByDesc('id');

        // Execute the final query and log what we actually get
        $finalResults = (clone $finalQuery)->limit(5)->get();

        Log::debug('[MentoringHistory] Final query results (sample)', [
            'final_sql' => $finalQuery->toSql(),
            'final_bindings' => $finalQuery->getBindings(),
            'final_count' => (clone $finalQuery)->count(),
            'final_sample' => $finalResults->map(fn ($s) => [
                'id' => $s->id,
                'position' => $s->position,
                'mentor_id' => $s->mentor_id,
                'taken_date' => $s->taken_date,
                'filed' => $s->filed,
            ])->all(),
        ]);

        return $finalQuery;
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

        $viewAll = $policy->viewAll($user);
        $result = [];

        if ($this->category === MentorPermissionService::ALL_CATEGORIES) {
            if ($viewAll) {
                $result = app(MentorPermissionService::class)
                    ->getAllCtsCallsignsForCategories($this->getVisibleCategories());
                Log::debug('[MentoringHistory] getVisibleCtsPositions ALL + viewAll', [
                    'count' => count($result),
                    'sample' => array_slice($result, 0, 10),
                ]);
            } else {
                $result = $user->getAllAssignedCallsigns();
                Log::debug('[MentoringHistory] getVisibleCtsPositions ALL (no viewAll)', [
                    'count' => count($result),
                    'sample' => array_slice($result, 0, 10),
                ]);
            }

            return $result;
        }

        if (empty($this->category)) {
            Log::debug('[MentoringHistory] getVisibleCtsPositions empty category → []');

            return [];
        }

        $result = $policy->visibleCtsPositionsForCategory($user, $scope, $this->category);
        Log::debug('[MentoringHistory] getVisibleCtsPositions specific category', [
            'category' => $this->category,
            'count' => count($result),
            'sample' => array_slice($result, 0, 10),
        ]);

        return $result;
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
