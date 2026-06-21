<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Filament\Training\Support\MentoringTrainingGroupBadgeColor;
use App\Filament\Training\Support\TrainingMemberAccountSearch;
use App\Models\Cts\Member;
use App\Models\Cts\Session;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentoringSessionsService;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
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

    protected static ?string $slug = 'mentoring/upcoming-sessions';

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
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
        return [
            ActionGroup::make([
                Action::make('reallocate')
                    ->label('Reallocate')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->modalHeading(fn (Session $record) => "Reallocate Session: {$record->student->name}")
                    ->modalDescription('Select a new mentor for this session.')
                    ->modalSubmitActionLabel('Reallocate Session')
                    ->visible(fn (Session $record): bool => auth()->user()?->can('reallocate', $record) ?? false)
                    ->form(fn (Session $record): array => [
                        TextInput::make('current_mentor')
                            ->label('Current Mentor')
                            ->disabled()
                            ->default($record->mentor?->name.' ('.$record->mentor?->cid.')'),

                        Select::make('new_mentor_cid')
                            ->label('New Mentor')
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) use ($record): array {
                                $category = app(MentorPermissionService::class)->resolveCategoryForCtsCallsign($record->position);

                                if (! $category) {
                                    return [];
                                }

                                $eligibleAccountIds = app(MentorPermissionService::class)
                                    ->accountsWithMentoringInCategoryQuery($category)
                                    ->pluck('id')
                                    ->toArray();

                                return collect(TrainingMemberAccountSearch::searchAccountsForSelect($search))
                                    ->filter(fn ($label, $cid) => in_array((int) $cid, $eligibleAccountIds, true) && (int) $cid !== $record->mentor?->cid)
                                    ->all();
                            })
                            ->getOptionLabelUsing(fn ($value): string => Member::where('cid', $value)->first()?->name." ({$value})")
                            ->required(),

                        Textarea::make('reason')
                            ->label('Reason for reallocation')
                            ->required()
                            ->minLength(10)
                            ->maxLength(1000),
                    ])
                    ->action(function (array $data, Session $record, MentoringSessionsService $mentoringService) {
                        try {
                            $success = $mentoringService->reallocateSession($record->id, $data['new_mentor_cid'], auth()->user(), $data['reason']);

                            if ($success) {
                                Notification::make()
                                    ->title('Session Reallocated')
                                    ->body("The session with {$record->student->name} has been reallocated to the new mentor.")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('Reallocation Failed')
                                    ->body('Could not reallocate the session. It may have been modified or is no longer valid.')
                                    ->danger()
                                    ->send();
                            }
                        } catch (AuthorizationException $e) {
                            Notification::make()
                                ->title('Reallocation Failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]),
        ];
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

    protected function getPositionColumnBadgeColor(): string|array|\Closure
    {
        if ($this->category !== MentorPermissionService::ALL_CATEGORIES) {
            return 'gray';
        }

        return fn ($record) => MentoringTrainingGroupBadgeColor::forCtsCallsign($record->position);
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
