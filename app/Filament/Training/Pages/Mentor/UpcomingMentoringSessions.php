<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class UpcomingMentoringSessions extends BaseMentoringHistoryPage
{
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
        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->firstVisibleCategory() ?? '';
        }
    }

    protected function getHeaderActions(): array
    {
        $allCategories = collect(MentorPermissionService::atcCategories())
            ->merge(MentorPermissionService::pilotCategories());

        return [
            ActionGroup::make(
                $allCategories
                    ->filter(fn (string $cat) => $this->canViewCategory($cat))
                    ->map(fn (string $cat) => Action::make('cat_'.str($cat)->slug('_'))
                        ->label($cat)
                        ->url(static::getUrl(['category' => $cat]))
                        ->icon($this->category === $cat ? 'heroicon-m-check' : null)
                    )
                    ->all()
            )
                ->label('Training Group: '.($this->category ?: 'All'))
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
        if (empty($this->category)) {
            return [];
        }

        return app(MentorPermissionService::class)->getAllCtsCallsignsForCategory($this->category);
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
