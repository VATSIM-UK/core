<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Services\Training\MentorPermissionService;
use Carbon\Carbon;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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

    public Collection $previousSessions;

    public function mount(): void
    {
        $this->session = Session::with([
            'student',
            'mentor',
            'reportSheets.field.category',
            'reportSheets.progSheet',
            'reportNote',
        ])->findOrFail($this->sessionId);

        $this->previousSessions = Session::query()
            ->where('student_id', $this->session->student_id)
            ->where('id', '!=', $this->session->id)
            ->where('position', $this->session->position) // TODO: I don't like this at all
            ->orderBy('taken_date', 'desc')
            ->limit(7)
            ->get();

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
                $rowClasses = 'pb-4 mb-4';
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
                ->schema($sheetRows)
                ->collapsible();
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
            ->state(['sessions' => $this->previousSessions])
            ->components([
                Section::make('Other Sessions')
                    ->schema([
                        RepeatableEntry::make('sessions')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('taken_date')
                                    ->hiddenLabel()
                                    ->date()
                                    ->url(fn (Session $record) => static::getUrl(['sessionId' => $record->id])),
                            ])
                            ->emptyTooltip('No previous sessions found.')
                            ->extraAttributes(['class' => 'no-border-repeatable']),
                    ]),
            ]);
    }
}
