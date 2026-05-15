<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Illuminate\Support\Collection;

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
            ->orderBy('taken_date', 'desc')
            ->get();

        $this->otherSessions = $this->allSessions->where('id', '!=', $this->session->id);

        $user = auth()->user();

        // Students may always view their own session report
        if ($this->session->student?->cid === $user->id) {
            return;
        }

        // Mentors may always view reports for sessions they conducted
        if ($this->session->mentor?->cid === $user->id) {
            return;
        }

        if (app(MentorPermissionService::class)->canMentorPosition($user, $this->session->position)) {
            return;
        }

        abort(403);
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

                            $trainingPlace = TrainingPlace::where('account_id', $record->student_id)->first();

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
        $groupedSheets = $this->session->reportSheets->groupBy(fn ($sheet) => $sheet->field->category->catName);

        $categorySections = [];

        foreach ($groupedSheets as $categoryName => $sheets) {

            $sheetRows = [];
            $totalSheets = count($sheets);
            $currentIndex = 0;

            foreach ($sheets as $index => $sheet) {
                $currentIndex++;
                $isLast = ($currentIndex === $totalSheets);

                $uniqueKey = $sheet->field_id ?? $index;

                // If it's the last field in the category we don't want the divider
                $rowClasses = 'pb-4 mb-6';
                if (! $isLast) {
                    $rowClasses .= ' border-b border-gray-200 dark:border-white/10';
                }

                $sheetRows[] = Grid::make(12)
                    ->schema([
                        TextEntry::make("field_name_{$uniqueKey}")
                            ->state($sheet->field->field)
                            ->hiddenLabel()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->columnSpan(10),

                        TextEntry::make("field_score_{$uniqueKey}")
                            ->hiddenLabel()
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

            $categorySections[] = Section::make($categoryName)
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
            ->state(['sessions' => $this->otherSessions->take(4)])
            ->components([
                Section::make('Other Sessions')
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

    protected function sessionsModalSchema(): array
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

    protected function getSessionsBySessionTab(): array
    {
        return $this->allSessions
            ->map(function (Session $session): Section {
                $grouped = $session->reportSheets
                    ->groupBy(fn ($sheet) => $sheet->field->category->catName);

                $rows = [];

                foreach ($grouped as $category => $sheets) {
                    $columns = $sheets
                        ->sortBy(fn ($sheet) => $sheet->field->sort_order ?? $sheet->field_id)
                        ->values()
                        ->map(function ($sheet) {
                            return Grid::make(1)
                                ->schema([
                                    TextEntry::make("field_{$sheet->id}")
                                        ->state($sheet->field->field)
                                        ->hiddenLabel()
                                        ->size(TextSize::Small)
                                        ->weight(FontWeight::SemiBold),

                                    TextEntry::make("grade_{$sheet->id}")
                                        ->state($sheet->field_score)
                                        ->hiddenLabel()
                                        ->badge(),
                                ]);
                        })
                        ->all();

                    $rows[] = Grid::make(6)
                        ->schema($columns);
                }

                $isCurrentSession = $session->id === $this->session->id;
                $sectionHeader = Carbon::parse($session->taken_date)->format('d/m/Y');
                $sectionDescription = $session->mentor?->account?->name;

                return Section::make($sectionHeader)
                    ->description($sectionDescription)
                    ->headerActions([
                        Action::make("viewReport{$session->id}")
                            ->label('View Report')
                            ->icon('heroicon-o-arrow-top-right-on-square')
                            ->link()
                            ->url(static::getUrl(['sessionId' => $session->id]))
                            ->openUrlInNewTab(! $isCurrentSession)
                            ->hidden($isCurrentSession),
                    ])
                    ->schema($rows);
            })
            ->all();
    }

    protected function getSessionsByCriteriaTab(): array
    {
        $sessions = $this->allSessions->sortBy('taken_date');
        $sessionCount = $sessions->count();

        if ($sessionCount === 0) {
            return [TextEntry::make('empty')->hiddenLabel()->state('No sessions found.')];
        }

        $criteriaData = [];
        foreach ($sessions as $session) {
            foreach ($session->reportSheets as $sheet) {
                $catName = $sheet->field?->category?->catName;

                $fieldId = $sheet->field_id;
                $fieldName = $sheet->field?->field;
                $sortOrder = $sheet->field?->sort_order ?? $fieldId;

                if (! isset($criteriaData[$catName][$fieldId])) {
                    $criteriaData[$catName][$fieldId] = [
                        'name' => $fieldName,
                        'sort' => $sortOrder,
                        'scores' => [],
                    ];
                }

                $criteriaData[$catName][$fieldId]['scores'][$session->id] = $sheet->field_score;
            }
        }

        $sections = [];

        foreach ($criteriaData as $catName => $fields) {
            uasort($fields, fn ($a, $b) => $a['sort'] <=> $b['sort']);

            $fieldRows = [];

            $headerEntries = [
                // Blank entry to fill the space in the column
                TextEntry::make("header_cat_{$catName}")
                    ->state('')
                    ->hiddenLabel(),
            ];

            foreach ($sessions as $session) {
                $isCurrentSession = $session->id === $this->session->id;

                $headerEntries[] = TextEntry::make("header_date_{$session->id}_{$catName}")
                    ->state(Carbon::parse($session->taken_date)->format('d/m/Y'))
                    ->hiddenLabel()
                    ->url($isCurrentSession ? null : static::getUrl(['sessionId' => $session->id]))
                    ->openUrlInNewTab()
                    ->size(TextSize::Small)
                    ->weight(FontWeight::SemiBold)
                    ->color($isCurrentSession ? Color::Gray : 'white');
            }

            $fieldRows[] = Grid::make($sessionCount + 1)
                ->schema($headerEntries);

            foreach ($fields as $fieldId => $data) {
                $rowEntries = [
                    TextEntry::make("criteria_{$catName}_{$fieldId}")
                        ->state($data['name'])
                        ->hiddenLabel()
                        ->size(TextSize::Small)
                        ->weight(FontWeight::SemiBold),
                ];

                foreach ($sessions as $session) {
                    $score = $data['scores'][$session->id];
                    $rowEntries[] = TextEntry::make("score_{$catName}_{$fieldId}_{$session->id}")
                        ->state($score)
                        ->hiddenLabel()
                        ->badge();
                }

                $fieldRows[] = Grid::make($sessionCount + 1)->schema($rowEntries)->extraAttributes([
                    'class' => 'items-center border-b border-gray-200 dark:border-white/10 py-4',
                ]);
            }

            $sections[] = Section::make($catName)->schema([Grid::make(1)->schema($fieldRows)->gap(false)]);
        }

        return $sections;
    }
}
