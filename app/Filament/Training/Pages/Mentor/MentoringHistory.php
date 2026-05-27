<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\Mentor\Base\BaseMentoringHistoryPage;
use App\Filament\Training\Pages\Mentor\Concerns\RemembersTrainingGroupCategory;
use App\Models\Cts\Session;
use App\Models\Training\Mentoring\MentoringScope;
use App\Policies\Training\Mentoring\MentoringPolicy;
use App\Repositories\Cts\SessionRepository;
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

        if (empty($this->category) || ! $this->canViewCategory($this->category)) {
            $this->category = $this->firstVisibleCategory() ?? '';
        }

        $this->saveCategoryToSession();
    }

    protected function getHeaderActions(): array
    {
        $availableCategories = auth()->user()->getAvailableMentoringCategories();

        return [
            ActionGroup::make(
                collect($availableCategories)
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

        if ($this->category) {
            $policy = app(MentoringPolicy::class);

            return $policy->visibleCtsPositionsForCategory($user, new MentoringScope, $this->category);
        }

        return $user->getAllAssignedCallsigns();
    }

    private function canViewCategory(string $category): bool
    {
        return auth()->user()?->can('viewCategory', [new MentoringScope, $category]) ?? false;
    }

    private function firstVisibleCategory(): ?string
    {
        return collect(auth()->user()->getAvailableMentoringCategories())->first();
    }
}
