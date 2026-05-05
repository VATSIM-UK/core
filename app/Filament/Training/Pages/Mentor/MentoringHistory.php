<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Repositories\Cts\SessionRepository;
use App\Services\Training\MentorPermissionService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class MentoringHistory extends BaseMentoringHistoryPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected string $view = 'filament.training.pages.mentoring-history';

    protected static ?int $navigationSort = 30;

    protected static string|\UnitEnum|null $navigationGroup = 'Mentoring';

    protected static ?string $title = 'Mentoring History';

    #[Url]
    public string $category = '';

    public static function canAccess(): bool
    {
        // If a user has any mentoring permissions they are allowed to view this page
        return auth()->user()->mentorTrainingPositions()->exists();
    }

    public function mount(): void
    {
        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->firstVisibleCategory() ?? '';
        }
    }

    protected function getHeaderActions(): array
    {
        $allCategories = collect(MentorPermissionService::atcCategories())->merge(MentorPermissionService::pilotCategories());

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
                ->label("Training Group: {$this->category}")
                ->icon('heroicon-m-chevron-down')
                ->color('gray')
                ->button(),
        ];
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
        return app(MentorPermissionService::class)->getAssignedCtsCallsigns(auth()->user(), $this->category);
    }

    private function canViewCategory(string $category): bool
    {
        $assignedCallsigns = app(MentorPermissionService::class)->getAssignedCtsCallsigns(auth()->user(), $category);

        return count($assignedCallsigns) > 0;
    }

    private function firstVisibleCategory(): ?string
    {
        return collect(MentorPermissionService::atcCategories())->merge(MentorPermissionService::pilotCategories())->first(fn (string $cat) => $this->canViewCategory($cat));
    }
}
