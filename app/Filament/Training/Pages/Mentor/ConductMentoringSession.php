<?php

declare(strict_types=1);

namespace App\Filament\Training\Pages\Mentor;

use App\Enums\FieldScore;
use App\Filament\Forms\Components\TrainingRichEditor;
use App\Filament\Training\Concerns\InteractsWithCtsRichEditorNotes;
use App\Filament\Training\Concerns\InteractsWithTrainingConductAutosave;
use App\Filament\Training\Support\MentoringReportLayout;
use App\Models\Cts\ProgSheetField;
use App\Models\Cts\ReportSheet;
use App\Models\Cts\Session;
use App\Models\NetworkData\Atc as NetworkdataAtc;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Repositories\Cts\MentoringReportRepository;
use App\Services\Training\MentoringReportService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\TextSize;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\ValidationException;

class ConductMentoringSession extends Page implements HasForms, HasInfolists
{
    use AuthorizesRequests;
    use InteractsWithCtsRichEditorNotes;
    use InteractsWithForms;
    use InteractsWithInfolists;
    use InteractsWithTrainingConductAutosave;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.training.pages.conduct-mentoring-session';

    protected static ?string $slug = 'mentoring/conduct/{sessionId}';

    public ?array $data = [];

    public int $sessionId;

    public Session $session;

    /** @var array<int, FieldScore> */
    public array $previousScores = [];

    /** @var array<int, FieldScore> */
    public array $bestScores = [];

    public ?array $additionalCommentsData = ['body' => ''];

    protected function getForms(): array
    {
        return [
            'form',
            'additionalCommentsForm',
        ];
    }

    public function mount(): void
    {
        try {
            $this->session = Session::with(['student', 'mentor'])
                ->findOrFail($this->sessionId);
        } catch (ModelNotFoundException) {
            abort(403, 'You do not have permission to conduct this mentoring session.');
        }

        $this->authorize('conduct', $this->session);

        $repository = app(MentoringReportRepository::class);
        $service = app(MentoringReportService::class);

        $this->previousScores = $service->getPreviousScores($this->session);
        $this->bestScores = $service->getBestScores($this->session);

        $existingScores = $repository->getExistingScoresForSession($this->session);
        $existingNotes = $this->existingNotesByField();

        $criteriaData = $repository->getCriteriaFieldsForSession($this->session)
            ->mapWithKeys(function (ProgSheetField $field) use ($existingScores, $existingNotes) {
                $fieldId = $field->field_id;
                $defaultScore = $existingScores[$fieldId]
                    ?? $this->previousScores[$fieldId]
                    ?? FieldScore::NOT_APPLICABLE;

                return [
                    $fieldId => [
                        'score' => $defaultScore->value,
                        'notes' => $this->ctsRichEditorHtmlForHydration($existingNotes[$fieldId] ?? null),
                    ],
                ];
            })
            ->all();

        $this->form->fill(['criteria' => $criteriaData]);

        $storedComments = $repository->getExistingAdditionalComments($this->session);
        if ($storedComments !== null) {
            $this->additionalCommentsForm->fill([
                'body' => $this->ctsRichEditorHtmlForHydration($storedComments),
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(fn () => $this->hasUnsavedChanges ? 'Save' : 'Saved')
                ->icon(fn () => $this->hasUnsavedChanges ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                ->color(fn () => $this->hasUnsavedChanges ? 'warning' : 'success')
                ->iconSize(IconSize::Large)
                ->action(fn () => $this->save()),
            Action::make('markNoShow')
                ->label('Mark no-show')
                ->color('danger')
                ->icon('heroicon-o-user-minus')
                ->visible(fn () => app(MentoringReportService::class)->canMarkNoShow($this->session))
                ->requiresConfirmation()
                ->modalHeading('Mark session as no-show')
                ->modalDescription(fn () => $this->noShowModalDescription())
                ->schema(fn () => $this->noShowModalForm())
                ->action(fn (array $data) => $this->markNoShow($data)),
        ];
    }

    public function sessionDetailsInfolist(Schema $schema): Schema
    {
        $syllabusUrl = $this->relevantSyllabusUrl();

        return $schema
            ->record($this->session)
            ->components([
                Section::make('Session Details')
                    ->columnSpanFull()
                    ->headerActions([
                        Action::make('viewSyllabus')
                            ->label('View Syllabus')
                            ->icon('heroicon-m-document-text')
                            ->color('gray')
                            ->size('sm')
                            ->url($syllabusUrl)
                            ->openUrlInNewTab()
                            ->visible(fn () => filled($syllabusUrl)),
                    ])
                    ->schema([
                        TextEntry::make('student')
                            ->label('Student')
                            ->getStateUsing(fn () => "{$this->session->student->name} ({$this->session->student->cid})"),
                        TextEntry::make('mentor')
                            ->label('Mentor')
                            ->getStateUsing(fn () => "{$this->session->mentor->name} ({$this->session->mentor->cid})"),
                        TextEntry::make('position')
                            ->label('Position'),
                        TextEntry::make('schedule')
                            ->label('Date & Time')
                            ->getStateUsing(fn () => "{$this->session->taken_date} | {$this->session->taken_from} - {$this->session->taken_to}"),
                        Callout::make('adjacent_atc')
                            ->visible(fn () => $this->adjacentAtcPositions->isNotEmpty())
                            ->icon('heroicon-m-signal')
                            ->color('primary')
                            ->columnSpanFull()
                            ->heading('Adjacent ATC Online')
                            ->description(
                                $this->adjacentAtcPositions
                                    ->map(fn (NetworkdataAtc $atc) => $atc->callsign)
                                    ->implode(', ')
                            ),
                    ])->columns(2),
            ]);
    }

    public function getAdjacentAtcPositionsProperty(): \Illuminate\Support\Collection
    {
        return NetworkdataAtc::adjacentPositionsForMentoringSession($this->session);
    }

    public function form(Schema $schema): Schema
    {
        $repository = app(MentoringReportRepository::class);
        $fields = $repository->getCriteriaFieldsForSession($this->session);
        $grouped = $fields->groupBy(fn (ProgSheetField $field) => $field->category?->catName ?? 'Uncategorized');

        $categorySections = $grouped->map(function ($categoryFields, string $categoryName) {
            $rows = $categoryFields->values()
                ->map(fn (ProgSheetField $field) => $this->criterionEntryGrid($field))
                ->all();

            return Section::make(MentoringReportLayout::categorySectionTitle($categoryName))
                ->schema($rows);
        })->values()->all();

        return $schema
            ->components([
                Section::make('Session Report')
                    ->columnSpanFull()
                    ->schema($categorySections),
            ])
            ->statePath('data');
    }

    public function additionalCommentsForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('additionalCommentsData')
            ->components([
                Section::make('Additional Comments')
                    ->columnSpanFull()
                    ->schema([
                        $this->conductSessionRichEditor(
                            $this->mentoringReportNotesEditor(TrainingRichEditor::make('body'))
                                ->columnSpanFull()
                                ->extraInputAttributes(['style' => 'min-height: 200px;']),
                            fn ($state): mixed => $this->markDirty(),
                        ),
                        Actions::make([
                            Action::make('submit_report')
                                ->label('Submit Report')
                                ->icon('heroicon-o-check')
                                ->color('primary')
                                ->requiresConfirmation()
                                ->action(fn () => $this->submitReport()),
                        ])->alignment(Alignment::End)->columnSpanFull(),
                    ]),
            ]);
    }

    private function criterionEntryGrid(ProgSheetField $field): Grid
    {
        $fieldId = $field->field_id;
        $best = $this->bestScores[$fieldId] ?? FieldScore::NOT_SCORED;
        $previous = $this->previousScores[$fieldId] ?? FieldScore::NOT_SCORED;

        return Grid::make(14)
            ->schema([
                TextEntry::make("criteria_display_{$fieldId}_name")
                    ->state($field->field)
                    ->hiddenLabel()
                    ->size(TextSize::Large)
                    ->weight(FontWeight::Bold)
                    ->columnSpan(8),
                TextEntry::make("criteria_display_{$fieldId}_best")
                    ->label('Best')
                    ->state($best)
                    ->badge()
                    ->icon('heroicon-m-trophy')
                    ->columnSpan(2),
                TextEntry::make("criteria_display_{$fieldId}_previous")
                    ->label('Previous')
                    ->state($previous)
                    ->badge()
                    ->icon('heroicon-m-clock')
                    ->columnSpan(2),
                Select::make("criteria.{$fieldId}.score")
                    ->label('Current')
                    ->options(FieldScore::mentoringConductOptions())
                    ->required()
                    ->columnSpan(2)
                    ->live()
                    ->afterStateUpdated(fn () => $this->markDirty(skipRender: true)),
                $this->conductSessionRichEditor(
                    $this->mentoringReportNotesEditor(TrainingRichEditor::make("criteria.{$fieldId}.notes"))
                        ->default('<p></p>')
                        ->columnSpan(12)
                        ->extraInputAttributes(['style' => 'min-height: 150px;']),
                ),
            ])
            ->extraAttributes(['class' => MentoringReportLayout::CRITERION_ROW_CLASSES]);
    }

    public function save(bool $withNotification = true): void
    {
        $this->isSaving = true;

        try {
            $criteriaState = $this->form->getState()['criteria'] ?? [];
            $criteriaData = collect($criteriaState)->mapWithKeys(function (array $item, $fieldId) {
                return [
                    (int) $fieldId => [
                        'score' => FieldScore::from((int) $item['score']),
                        'notes' => $this->ctsRichContentNotesForCts($item['notes'] ?? null),
                    ],
                ];
            })->all();

            app(MentoringReportService::class)->saveDraft(
                $this->session,
                $criteriaData,
                $this->ctsRichContentNotesForCts($this->additionalCommentsData['body'] ?? ''),
            );

            $this->hasUnsavedChanges = false;

            if ($withNotification) {
                Notification::make()
                    ->title('Mentoring report saved')
                    ->success()
                    ->send();
            } else {
                $this->skipRender();
            }
        } finally {
            $this->isSaving = false;
        }
    }

    public function submitReport(): void
    {
        $this->save(withNotification: false);

        try {
            app(MentoringReportService::class)->submit($this->session->fresh());
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Cannot submit mentoring report')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Mentoring report submitted')
            ->success()
            ->send();

        $this->redirect(ViewMentoringReport::getUrl(['sessionId' => $this->session->id]));
    }

    public function markNoShow(array $data): void
    {
        $wasShortNotice = app(MentoringReportService::class)->wasBookedWithShortNotice($this->session);
        $confirmed = (bool) ($data['student_confirmed_discord'] ?? false);

        try {
            app(MentoringReportService::class)->markNoShow($this->session->fresh(), $confirmed);
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Unable to mark no-show')
                ->body(collect($exception->errors())->flatten()->first())
                ->danger()
                ->send();

            return;
        }

        if ($wasShortNotice && ! $confirmed) {
            Notification::make()
                ->title('Session cancelled')
                ->body('The session was cancelled on your behalf. No no-show has been recorded for the student.')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Session marked as no-show')
                ->success()
                ->send();
        }

        $this->redirect(Mentoring::getUrl());
    }

    private function noShowModalDescription(): string
    {
        if (app(MentoringReportService::class)->wasBookedWithShortNotice($this->session)) {
            return 'This session was booked with less than 24 hours notice. Did the student confirm their non-attendance via Discord?';
        }

        return 'Are you sure you want to mark this session as a no-show? The report will be filed automatically.';
    }

    /**
     * @return array<int, \Filament\Forms\Components\Component>
     */
    private function noShowModalForm(): array
    {
        if (! app(MentoringReportService::class)->wasBookedWithShortNotice($this->session)) {
            return [];
        }

        return [
            Toggle::make('student_confirmed_discord')
                ->label('Student confirmed non-attendance via Discord')
                ->required(),
        ];
    }

    /**
     * @return array<int, string|null>
     */
    private function existingNotesByField(): array
    {
        return ReportSheet::query()
            ->where('seshid', $this->session->id)
            ->where('field_id', '!=', 0)
            ->pluck('notes', 'field_id')
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
