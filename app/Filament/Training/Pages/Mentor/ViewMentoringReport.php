<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Enums\FieldScore;
use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Livewire\Training\CriteriaCategoryTable;
use App\Livewire\Training\SessionCriteriaTable;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ViewMentoringReport extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.training.pages.view-mentoring-report';

    protected static ?string $slug = 'mentoring/report/{sessionId}';

    public int $sessionId;

    public Session $session;

    public Collection $allSessions;

    public Collection $otherSessions;

    public int $otherSessionsPage = 1;

    public function mount(): void
    {
        $this->session = Session::with([
            'student',
            'mentor',
            'reportSheets.field.category',
            'reportSheets.progSheet',
            'reportNote',
        ])->findOrFail($this->sessionId);

        $this->allSessions = Session::query()
            ->with(['mentor', 'reportSheets.field.category'])
            ->where('student_id', $this->session->student_id)
            ->where('position', $this->session->position)
            ->where('progress_sheet_id', $this->session->progress_sheet_id)
            ->whereNotNull('filed')
            ->orderBy('taken_date', 'desc')
            ->get();

        $this->otherSessions = $this->allSessions->where('id', '!=', $this->session->id);

        $user = auth()->user();

        // Temporary beta permission
        if (! app()->runningUnitTests() && ! auth()->user()?->can('training.beta')) {
            abort(403, 'You do not have permission to view this mentoring report.');
        }

        // Students may always view their own session report
        if ($this->session->student_id === $user->id) {
            return;
        }

        // Mentors may always view reports for sessions they conducted
        if ($this->session->mentor_id === $user->id) {
            return;
        }

        if ($user->canMentorPosition($this->session->position)) {
            return;
        }

        abort(403, 'You do not have permission to view this mentoring report.');
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->record($this->session)->components([
            Section::make('Session Summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('student.account.name')
                        ->label('Student')
                        ->helperText(fn (Session $record) => $record->student->account->id)
                        ->url(function (Session $record) {
                            $user = auth()->user();
                            if (! $user || ! $user->can('training-places.view.*')) {
                                return null;
                            }

                            $accountId = $record->student?->account->id;

                            if (! $accountId) {
                                return null;
                            }

                            $trainingPlace = TrainingPlace::where('account_id', $accountId)->first();

                            return $trainingPlace ? ViewTrainingPlace::getUrl(['trainingPlaceId' => $trainingPlace->id]) : null;
                        }),

                    TextEntry::make('mentor.account.name')
                        ->label('Mentor')
                        ->helperText(fn (Session $record) => $record->mentor->account->id),

                    TextEntry::make('position')
                        ->label('Position & Time')
                        ->helperText(fn (Session $record) => Carbon::parse($record->taken_date)->format('d/m/Y').' | '.Carbon::parse($record->taken_from)->format('H:i').' - '.Carbon::parse($record->taken_to)->format('H:i')),
                ]),

            Section::make('Additional Comments')
                ->visible(fn () => (bool) $this->session->reportNote)
                ->schema([
                    TextEntry::make('reportNote.text')
                        ->hiddenLabel()
                        ->html()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public function reportInfolist(Schema $schema): Schema
    {
        $scoreMap = [];
        foreach ($this->allSessions as $sess) {
            foreach ($sess->reportSheets as $s) {
                $fieldId = $s->field_id ?? 'unknown';
                $scoreMap[$fieldId][$sess->id] = $s->field_score;
            }
        }

        $previousSession = $this->otherSessions
            ->where('taken_date', '<=', $this->session->taken_date)
            ->sortByDesc('taken_date')
            ->first();

        $groupedSheets = $this->session->reportSheets->groupBy(fn ($s) => $s->field?->category?->catName ?? 'Uncategorized');
        $categorySections = [];

        foreach ($groupedSheets as $categoryName => $sheets) {
            $sheetRows = [];
            $totalSheets = count($sheets);
            $currentIndex = 0;

            foreach ($sheets as $index => $sheet) {
                $currentIndex++;
                $isLast = ($currentIndex === $totalSheets);
                $uniqueKey = $sheet->field_id ?? $index;

                $fieldScores = collect($scoreMap[$sheet->field_id] ?? []);
                $previousScore = $previousSession ? ($fieldScores->get($previousSession->id) ?? FieldScore::NOT_SCORED) : FieldScore::NOT_SCORED;
                $bestScore = $fieldScores->isNotEmpty() ? $fieldScores->sortByDesc(fn (FieldScore $s) => $s->value)->first() : FieldScore::NOT_SCORED;

                $rowClasses = 'pb-4 mb-6';
                if (! $isLast) {
                    $rowClasses .= ' border-b border-gray-200 dark:border-white/10';
                }

                $sheetRows[] = Grid::make(14)
                    ->schema([
                        TextEntry::make("field_name_{$uniqueKey}")
                            ->state($sheet->field?->field ?? 'Unknown Field')
                            ->hiddenLabel()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpan(8),

                        TextEntry::make("field_best_{$uniqueKey}")
                            ->label('Best')
                            ->state($bestScore)
                            ->badge()
                            ->icon('heroicon-m-trophy')
                            ->columnSpan(2),

                        TextEntry::make("field_previous_{$uniqueKey}")
                            ->label('Previous')
                            ->state($previousScore)
                            ->badge()
                            ->icon('heroicon-m-clock')
                            ->columnSpan(2),

                        TextEntry::make("field_score_{$uniqueKey}")
                            ->label('Current')
                            ->state($sheet->field_score)
                            ->badge()
                            ->columnSpan(2),

                        TextEntry::make("field_notes_{$uniqueKey}")
                            ->label('Notes')
                            ->state($sheet->notes)
                            ->hiddenLabel()
                            ->html()
                            ->extraAttributes(['style' => 'word-break:break-word'])
                            ->columnSpan(12)
                            ->hidden(blank($sheet->notes)),
                    ])
                    ->extraAttributes(['class' => $rowClasses]);
            }

            $categorySections[] = Section::make(new HtmlString("<span class='text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white'>{$categoryName}</span>"))
                ->schema($sheetRows);
        }

        return $schema->record($this->session)->components([
            Section::make('Session Report')
                ->columnSpanFull()
                ->schema(count($categorySections) > 0 ? $categorySections : [TextEntry::make('empty')->label('')->state('No report data found for this session.')]),
        ]);
    }

    public function previousSessionsInfolist(Schema $schema): Schema
    {
        return $schema
            ->state(fn () => ['sessions' => $this->otherSessions->forPage($this->otherSessionsPage, 3)->values()])
            ->components([
                Section::make('Other Sessions')
                    ->headerActions([
                        Action::make('previousPage')
                            ->icon('heroicon-m-chevron-left')
                            ->hiddenLabel()
                            ->link()
                            ->tooltip('Previous 3 sessions')
                            ->disabled(fn () => $this->otherSessionsPage <= 1)
                            ->action(fn () => $this->otherSessionsPage--)
                            ->visible(fn () => $this->otherSessions->count() > 3),

                        Action::make('nextPage')
                            ->icon('heroicon-m-chevron-right')
                            ->hiddenLabel()
                            ->link()
                            ->tooltip('Next 3 sessions')
                            ->disabled(fn () => ($this->otherSessionsPage * 3) >= $this->otherSessions->count())
                            ->action(fn () => $this->otherSessionsPage++)
                            ->visible(fn () => $this->otherSessions->count() > 3),
                    ])
                    ->schema([
                        RepeatableEntry::make('sessions')
                            ->hiddenLabel()
                            ->columns(1)
                            ->schema([
                                TextEntry::make('taken_date')
                                    ->hiddenLabel()
                                    ->date()
                                    ->size(TextSize::Small)
                                    ->weight(FontWeight::SemiBold)
                                    ->helperText(fn (Session $record) => $record->mentor?->account?->name)
                                    ->url(fn (Session $record) => static::getUrl(['sessionId' => $record->id])),
                            ]),

                        TextEntry::make('view_more')
                            ->hiddenLabel()
                            ->state('View Sessions Overview')
                            ->color('primary')
                            ->weight(FontWeight::SemiBold)
                            ->url(null)
                            ->action($this->viewSessionsOverviewAction()),
                    ]),
            ]);
    }

    public function viewSessionsOverviewAction(): Action
    {
        return Action::make('viewSessionsOverview')
            ->label('View Overview')
            ->link()
            ->modalHeading('All Sessions Overview')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->schema($this->sessionsModalSchema());
    }

    public function sessionsModalSchema(): array
    {
        return [
            Tabs::make('Sessions Overview')
                ->tabs([
                    Tabs\Tab::make('By Criteria')
                        ->schema($this->getSessionsByCriteriaTab()),

                    Tabs\Tab::make('By Session')
                        ->schema($this->getSessionsBySessionTab()),
                ]),
        ];
    }

    public function getSessionsBySessionTab(): array
    {
        return $this->allSessions
            ->map(function (Session $session): Section {
                $isCurrentSession = $session->id === $this->session->id;

                return Section::make(Carbon::parse($session->taken_date)->format('d/m/Y'))
                    ->description($session->mentor?->account?->name)
                    ->headerActions([
                        Action::make("viewReport{$session->id}")
                            ->label('View Report')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->link()
                            ->url(static::getUrl(['sessionId' => $session->id]))
                            ->openUrlInNewTab(! $isCurrentSession)
                            ->hidden($isCurrentSession),
                    ])
                    ->schema([
                        Livewire::make(SessionCriteriaTable::class, [
                            'sessionId' => $session->id,
                        ]),
                    ]);
            })
            ->all();
    }

    public function getSessionsByCriteriaTab(): array
    {
        $sessionIds = $this->allSessions->sortBy('taken_date')->pluck('id')->all();

        $categories = $this->allSessions
            ->flatMap(fn (Session $session) => $session->reportSheets)
            ->map(fn ($sheet) => $sheet->field?->category?->catName)
            ->filter()
            ->unique()
            ->values();

        if ($categories->isEmpty()) {
            return [
                TextEntry::make('no_sessions')
                    ->hiddenLabel()
                    ->state('No criteria found.'),
            ];
        }

        return $categories
            ->map(fn (string $catName) => Section::make($catName)->schema([
                Livewire::make(CriteriaCategoryTable::class, [
                    'currentSessionId' => $this->session->id,
                    'sessionIds' => $sessionIds,
                    'categoryName' => $catName,
                ]),
            ])
            )
            ->all();
    }
}
