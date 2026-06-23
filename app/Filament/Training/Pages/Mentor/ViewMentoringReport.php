<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Concerns\InteractsWithCtsRichEditorNotes;
use App\Filament\Training\Pages\MyTraining\MyMentoringHistory;
use App\Filament\Training\Pages\StudentOverview\ViewStudentOverview;
use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Support\MentoringReportLayout;
use App\Filament\Training\Support\MentoringReportScores;
use App\Livewire\Training\CriteriaCategoryTable;
use App\Livewire\Training\SessionCriteriaTable;
use App\Models\Cts\Session;
use App\Models\NetworkData\Atc as NetworkdataAtc;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;

class ViewMentoringReport extends Page implements HasInfolists
{
    use AuthorizesRequests;
    use InteractsWithCtsRichEditorNotes;
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

    public function getBreadcrumbs(): array
    {
        $category = session('mentoring.category', '');

        if (auth()->user()?->can('viewAny', Session::class)) {
            $url = MentoringHistory::getUrl(array_filter(['category' => $category]));
        } else {
            $url = MyMentoringHistory::getUrl();
        }

        return [
            $url => 'Mentoring History',
            '' => 'Session Report',
        ];
    }

    public function mount(): void
    {
        $this->session = Session::with([
            'student',
            'mentor',
            'reportSheets.field.category',
            'reportSheets.progSheet',
            'cancelReason',
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

        $this->authorize('view', $this->session);
    }

    public function infolist(Schema $schema): Schema
    {
        $syllabusUrl = $this->relevantSyllabusUrl();

        return $schema->record($this->session)->components([
            Section::make('Session Summary')
                ->columns(3)
                ->headerActions([
                    Action::make('viewSyllabus')
                        ->label('View Syllabus')
                        ->icon('heroicon-m-document-text')
                        ->color('gray')
                        ->size('sm')
                        ->url($syllabusUrl)
                        ->openUrlInNewTab()
                        ->visible(fn () => filled($syllabusUrl)),

                    Action::make('viewStudentOverview')
                        ->label('View Student Overview')
                        ->icon('heroicon-o-user')
                        ->color('gray')
                        ->size('sm')
                        ->url(function (): ?string {
                            $trainingPlace = TrainingPlace::where('account_id', $this->session->student->cid)
                                ->whereHas('trainingPosition', fn ($query) => $query->whereJsonContains('cts_positions', $this->session->position))
                                ->first();

                            if (! $trainingPlace) {
                                return null;
                            }

                            return ViewStudentOverview::getUrl(['trainingPlaceId' => $trainingPlace->id]);
                        })
                        ->openUrlInNewTab()
                        ->hidden(fn (): bool => ! TrainingPlace::where('account_id', $this->session->student->cid)
                            ->whereHas('trainingPosition', fn ($query) => $query->whereJsonContains('cts_positions', $this->session->position))
                            ->exists()),
                ])
                ->schema([
                    TextEntry::make('student.account.name')
                        ->label('Student')
                        ->helperText(fn (Session $record) => $record->student->account->id)
                        ->url(function (Session $record) {
                            $user = auth()->user();
                            if (! $user || ! $user->can('viewStudentTrainingPlace', Session::class)) {
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

                    Callout::make('adjacent_atc')
                        ->visible(fn (Session $record) => NetworkdataAtc::adjacentPositionsForMentoringSession($record)->isNotEmpty())
                        ->icon('heroicon-m-signal')
                        ->color('primary')
                        ->columnSpanFull()
                        ->heading('Adjacent ATC Online')
                        ->description(fn (Session $record) => NetworkdataAtc::adjacentPositionsForMentoringSession($record)
                            ->map(fn (NetworkdataAtc $atc) => $atc->callsign)
                            ->implode(', ')
                        ),

                    Callout::make('Session Cancelled')
                        ->heading(function (Session $record): string {
                            $cancelledBy = $record->cancelReason?->member?->name;

                            if (! $cancelledBy) {
                                return 'Session Cancelled';
                            }

                            return "Session Cancelled by {$cancelledBy}";
                        })
                        ->description(function (Session $record): string {
                            if (! $record->cancelReason) {
                                return 'This session was cancelled, but no reason was provided.';
                            }

                            return $record->cancelReason->reason;
                        })
                        ->warning()
                        ->footer(function (Session $record): array {
                            if (! $record->cancelReason || ! $record->cancelled_datetime) {
                                return [];
                            }

                            $sessionStart = Carbon::parse($record->taken_date.' '.$record->taken_from);
                            $cancelDate = Carbon::parse($record->cancelled_datetime);

                            $notice = $cancelDate->diffForHumans($sessionStart, ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2]).' notice given';

                            return [
                                Text::make('notice')
                                    ->content($notice),
                            ];
                        })
                        ->columnSpanFull()
                        ->visible(fn (Session $record) => $record->cancelled_datetime !== null),

                    Callout::make('Student No-Showed')
                        ->description('This session has been marked as a student no-show.')
                        ->columnSpanFull()
                        ->danger()
                        ->footer(function (Session $record): array {
                            $noShowCount = Session::query()
                                ->where('student_id', $record->student_id)
                                ->where('noShow', true)
                                ->count();

                            return [
                                Text::make('noShowCount')
                                    ->content("Total student no-shows recorded: {$noShowCount}")
                                    ->color('gray'),
                            ];
                        })
                        ->visible(fn (Session $record) => (bool) $record->noShow),
                ]),

            Section::make('Additional Comments')
                ->visible(fn () => $this->session->reportSheets->contains('field_id', 0))
                ->schema([
                    TextEntry::make('additional_comments')
                        ->hiddenLabel()
                        ->html()
                        ->columnSpanFull()
                        ->state(fn (Session $record) => $this->ctsPlainNotesForHtmlDisplay(
                            $record->reportSheets->firstWhere('field_id', 0)?->notes,
                        )),
                ]),
        ]);
    }

    public function reportInfolist(Schema $schema): Schema
    {
        $scoreMap = MentoringReportScores::scoreMapForSessions($this->allSessions);

        $previousSession = $this->otherSessions
            ->where('taken_date', '<=', $this->session->taken_date)
            ->sortByDesc('taken_date')
            ->first();

        $groupedSheets = $this->session->reportSheets->reject(fn ($s) => $s->field_id === 0)->groupBy(fn ($s) => $s->field?->category?->catName ?? 'Uncategorized');

        $categorySections = [];

        foreach ($groupedSheets as $categoryName => $sheets) {
            $sheetRows = [];

            foreach ($sheets as $index => $sheet) {
                $uniqueKey = $sheet->field_id ?? $index;

                $previousScore = MentoringReportScores::previousScore($scoreMap, $sheet->field_id, $previousSession);
                $bestScore = MentoringReportScores::bestScore($scoreMap, $sheet->field_id);
                $bestScoreSessionId = MentoringReportScores::bestScoreSessionId($scoreMap, $sheet->field_id);

                $sheetRows[] = Grid::make(14)
                    ->schema([
                        Grid::make(1)
                            ->extraAttributes(['class' => 'gap-0'])
                            ->schema([
                                TextEntry::make("field_name_{$uniqueKey}")
                                    ->state($sheet->field?->field ?? 'Unknown Field')
                                    ->hiddenLabel()
                                    ->size(TextSize::Large)
                                    ->weight(FontWeight::Bold)
                                    ->extraAttributes(['style' => 'margin-bottom:0.5px']),

                                TextEntry::make("field_notes_{$uniqueKey}")
                                    ->label('Notes')
                                    ->state($this->ctsPlainNotesForHtmlDisplay($sheet->notes))
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->extraAttributes(['style' => 'word-break:break-word'])
                                    ->hidden(blank($sheet->notes)),
                            ])->columnSpan(8),

                        TextEntry::make("field_best_{$uniqueKey}")
                            ->label('Best')
                            ->state($bestScore)
                            ->badge()
                            ->icon('heroicon-m-trophy')
                            ->url(function () use ($bestScoreSessionId, $bestScore, $sheet): ?string {
                                if (! $bestScoreSessionId || $bestScoreSessionId === $this->session->id) {
                                    return null;
                                }

                                if ($sheet->field_score === $bestScore) {
                                    return null;
                                }

                                return static::getUrl(['sessionId' => $bestScoreSessionId]);
                            })
                            ->openUrlInNewTab()
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
                    ])
                    ->extraAttributes(['class' => MentoringReportLayout::CRITERION_ROW_CLASSES]);
            }

            $categorySections[] = Section::make(MentoringReportLayout::categorySectionTitle($categoryName))
                ->schema($sheetRows);
        }

        return $schema->record($this->session)->components([
            Section::make('Session Report')
                ->columnSpanFull()
                ->visible(count($categorySections) > 0)
                ->schema($categorySections),
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
            ->reject(fn ($sheet) => $sheet->field_id === 0)
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

    /**
     * Resolves the syllabus link.
     */
    private function relevantSyllabusUrl(): ?string
    {
        $trainingPosition = TrainingPosition::query()
            ->whereJsonContains('cts_positions', $this->session->position)
            ->first();

        return $trainingPosition?->syllabus_url;
    }
}
