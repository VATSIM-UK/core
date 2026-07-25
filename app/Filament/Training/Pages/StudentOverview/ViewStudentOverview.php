<?php

namespace App\Filament\Training\Pages\StudentOverview;

use App\Livewire\Training\CriteriaCategoryTable;
use App\Livewire\Training\SessionCriteriaTable;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\TrainingProgressCalculator;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;

class ViewStudentOverview extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.training.pages.student-overview';

    protected string|\Filament\Support\Enums\Width|null $maxContentWidth = 'full';

    protected static ?string $slug = '/mentoring/student-overview/{trainingPlaceId}';

    public TrainingPlace $trainingPlace;

    public string $trainingPlaceId;

    private TrainingProgressCalculator $calculator;

    public function mount(): void
    {
        $this->trainingPlace = TrainingPlace::withTrashed()
            ->where('id', $this->trainingPlaceId)
            ->with(['account', 'waitingListAccount', 'trainingPosition.position'])
            ->firstOrFail();

        $position = $this->trainingPlace->trainingPosition?->position?->callsign;

        if ($position && ! $this->authorize('mentorPosition', [Session::class, $position])) {
            abort(403, 'You do not have permission to view this student overview.');
        }

        $this->calculator = new TrainingProgressCalculator($this->trainingPlace);
    }

    public function getTitle(): string|Htmlable
    {
        $name = $this->trainingPlace->account->name;
        $callsign = $this->trainingPlace->trainingPosition?->position?->callsign;

        return "Student Overview - {$name} ({$callsign})";
    }

    public function getBreadcrumbs(): array
    {
        return [
            ListStudentOverviews::getUrl() => 'Student Overviews',
            $this->getTitle(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->record($this->trainingPlace)->components([
            Section::make('Sessions Overview')
                ->columnSpanFull()
                ->schema($this->buildCriteriaTabsSchema()),
        ]);
    }

    private function buildCriteriaTabsSchema(): array
    {
        $progress = $this->calculator->calculate();

        $latestSessionId = $progress['latestSessionId'];
        $sessionIds = $progress['sessionIds'];
        $categories = collect($progress['categories'])->pluck('name')->all();

        $allSessionsContent = $categories ? array_map(fn ($categoryName) => Section::make($categoryName)
            ->compact()
            ->schema([
                Livewire::make(CriteriaCategoryTable::class, [
                    'currentSessionId' => $latestSessionId,
                    'sessionIds' => $sessionIds,
                    'categoryName' => $categoryName,
                    'greyCurrentSession' => false,
                ]),
            ]), $categories)
            : [Text::make('no_categories')->content('No completed sessions yet.')];

        $latestSessionContent = $latestSessionId ? Livewire::make(SessionCriteriaTable::class, ['sessionId' => $latestSessionId]) : Text::make('no_sessions')->content('No completed sessions yet.');

        return [
            Tabs::make('criteria_tabs')->tabs([
                Tab::make('All Sessions')->schema($allSessionsContent),
                Tab::make('Latest Session')->schema([$latestSessionContent]),
            ]),
        ];
    }

    public function getTrainingProgressData(): array
    {
        return $this->calculator->calculate();
    }

    public static function getProgressColor(int $percentage): string
    {
        return match (true) {
            $percentage >= 75 => '#22c55e',
            $percentage >= 50 => '#eab308',
            $percentage >= 25 => '#ef4444',
            default => '#6b7280',
        };
    }
}
