<?php

namespace App\Filament\Training\Pages\TheoryExam;

use App\Models\Cts\TheoryQuestion;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class TheoryExamQuestions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected string $view = 'filament.training.pages.theory-exam-questions';

    protected static string|\UnitEnum|null $navigationGroup = 'Theory';

    public string $level = 'S1';

    public array $levels = ['S1', 'S2', 'S3', 'C1'];

    protected array $userPermissionsTruthTable = [];

    public array $allowedLevels = [];

    public function mount(): void
    {
        $this->userPermissionsTruthTable = [
            's1' => auth()->user()->can('training.theory.manage.obs'),
            's2' => auth()->user()->can('training.theory.manage.twr'),
            's3' => auth()->user()->can('training.theory.manage.app'),
            'c1' => auth()->user()->can('training.theory.manage.ctr'),
        ];
        $this->allowedLevels = collect($this->userPermissionsTruthTable)->filter(fn ($value) => $value)->keys()->map(fn ($level) => strtoupper($level))->all();

        $requestedLevel = strtoupper(request()->get('level'));

        $this->level = in_array($requestedLevel, $this->allowedLevels) ? $requestedLevel : $this->allowedLevels[0] ?? 'S1';
    }

    public static function canAccess(): bool
    {

        return auth()->user()->can('training.theory.access');
    }

    protected function getHeaderActions(): array
    {
        $levelButtons = collect($this->levels)
            ->filter(fn ($level) => in_array($level, $this->allowedLevels))
            ->map(function ($level) {
                return Action::make($level)
                    ->label($level)
                    ->url(fn () => static::getUrl(['level' => $level]))
                    ->color($this->level === $level ? 'primary' : 'gray');
            })->all();

        $createButton = CreateAction::make('create')
            ->label('Create Question')
            ->modalHeading('Create Question')
            ->schema($this->getQuestionFormSchema())
            ->color('success')
            ->using(function (array $data, $action) {
                TheoryQuestion::create([
                    ...$data,
                    'status' => 1,
                    'add_by' => auth()->id(),
                    'add_date' => now(),
                    'edit_by' => auth()->id(),
                    'edit_date' => now(),
                ]);

                $action->success();

            })
            ->successNotificationTitle('Question created');

        return [
            ...$levelButtons,
            $createButton,
        ];
    }

    public function getTitle(): string
    {
        return "{$this->level} Theory Questions";
    }

    protected function baseQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return TheoryQuestion::query()->where('deleted', 0)->where('level', $this->level)->whereIn('level', $this->allowedLevels);
    }

    public function getSubheading(): string
    {
        $total = $this->baseQuery()->count();
        $active = $this->baseQuery()->where('status', 1)->count();
        $inactive = $total - $active;

        return "Total: {$total} | Active: {$active} | Inactive: {$inactive}";
    }

    public function table(Table $table): Table
    {

        return $table
            ->query($this->baseQuery())
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true)->searchable(),
                TextColumn::make('question')->wrap()->searchable(),
                IconColumn::make('status')
                    ->boolean()
                    ->label('Active')->sortable(),
            ])
            ->recordActions([
                Action::make('edit')
                    ->schema($this->getQuestionFormSchema())
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->modalHeading('Edit Question')
                    ->modalSubmitActionLabel('Update Question')
                    ->fillForm(fn ($record) => [
                        ...$record->toArray(),
                        'added_by' => $record->added_by_member?->account?->name ?? $record->add_by,
                        'added_date' => $record->add_date ? Carbon::parse($record->add_date)->isoFormat('LL') : null, // Date only (stored in db like this, not sure why)
                        'edited_by' => $record->edited_by_member?->account?->name ?? $record->edit_by,
                        'edited_date' => $record->edit_date ? Carbon::parse($record->edit_date)->isoFormat('lll') : null,
                    ])
                    ->action(function (array $data, $record, $action) {
                        $record->update([
                            ...$data,
                            'edit_by' => auth()->id(),
                            'edit_date' => now(),
                        ]);
                        $action->success();
                    })->successNotificationTitle('Question updated'),
                ActionGroup::make([
                    Action::make('toggleStatus')
                        ->icon(fn ($record) => $record->status ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->label(fn ($record) => $record->status ? 'Disable' : 'Enable')
                        ->color(fn ($record) => $record->status ? 'danger' : 'success')
                        ->action(function ($record, $action) {
                            $record->update([
                                'status' => ! $record->status,
                                'edit_by' => auth()->id(),
                                'edit_date' => now(),
                            ]);
                            $action->success();
                        })->successNotificationTitle('Question status updated'),
                    Action::make('stats')
                        ->icon('heroicon-o-chart-bar')
                        ->label('Stats')
                        ->color('warning')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->modalFooterActionsAlignment('end')
                        ->modalHeading(fn ($record) => "Question Statistics ({$record->id})")
                        ->schema(fn ($record) => $this->getQuestionStatsSchema($record)),
                    Action::make('delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-trash')
                        ->action(function ($record, $action) {
                            $record->update([
                                'deleted' => 1,
                                'edit_by' => auth()->id(),
                                'edit_date' => now(),
                            ]);
                            $action->success();
                        })->successNotificationTitle('Question deleted'),
                ])->icon('heroicon-o-cog-6-tooth')->color('secondary'),
            ])
            ->paginated(['10', '25', '50'])
            ->defaultPaginationPageOption(25);
    }

    protected function getQuestionFormSchema(): array
    {
        return [
            Textarea::make('question')->rows(2)->autosize()->required()->columnSpanFull(),

            TextInput::make('option_1')->required()->columnSpanFull()
                ->suffixAction($this->correctAnswerAction(1)),
            TextInput::make('option_2')->required()->columnSpanFull()
                ->suffixAction($this->correctAnswerAction(2)),
            TextInput::make('option_3')->required()->columnSpanFull()
                ->suffixAction($this->correctAnswerAction(3)),
            TextInput::make('option_4')->required()->columnSpanFull()
                ->suffixAction($this->correctAnswerAction(4)),

            ToggleButtons::make('level')
                ->disableLabel(true)
                ->options(
                    collect([
                        'S1' => 'S1',
                        'S2' => 'S2',
                        'S3' => 'S3',
                        'C1' => 'C1',
                    ])->only($this->allowedLevels)->all())
                ->required()
                ->inline()
                ->default($this->level)
                ->extraAttributes(['class' => 'justify-center']),

            Hidden::make('answer')->required(),

            Section::make('Additional Information')
                ->collapsed()
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('added_by')->disabled(),
                    TextInput::make('added_date')->disabled(),
                    TextInput::make('edited_by')->disabled(),
                    TextInput::make('edited_date')->disabled(),
                ]),
        ];
    }

    protected function correctAnswerAction(int $optionNumber): Action
    {
        return Action::make("correct_{$optionNumber}")
            ->iconButton()
            ->size('lg')
            ->icon('heroicon-o-check-circle')
            ->color(fn (Get $get) => $get('answer') == $optionNumber ? 'success' : 'gray')
            ->action(fn (Set $set) => $set('answer', $optionNumber));
    }

    protected function getQuestionStatsSchema(TheoryQuestion $question): array
    {
        $answers = $question->answers()->select([
            'answer_given',
            'answer_correct',
            'correct',
        ])->get();

        $total = $answers->count();
        $correct = $answers->where('correct', 1)->count();
        $incorrect = $total - $correct;

        $percentage = fn (int $value) => $total > 0 ? round(($value / $total) * 100, 1) : 0;

        $optionStats = [];

        foreach (range(1, 4) as $option) {
            $count = $answers->where('answer_given', $option)->count();
            $optionStats[$option] = [
                'count' => $count,
                'percentage' => $percentage($count),
            ];
        }

        return [
            Section::make('Question')->schema([
                TextEntry::make('question')->label('Question')->state($question->question)->columnSpanFull(),
                TextEntry::make('level')->label('Level')->badge()->color(fn ($state) => match ($state) {
                    'S1' => \Filament\Support\Colors\Color::Green,
                    'S2' => \Filament\Support\Colors\Color::Blue,
                    'S3' => \Filament\Support\Colors\Color::Indigo,
                    'C1' => \Filament\Support\Colors\Color::Purple,
                    default => 'gray',
                })->state($question->level)->columnSpanFull(),
            ]),
            Section::make('Overview')->columns(3)->schema([
                TextEntry::make('total')->label('Times Used')->color('info')->badge()->state($total),
                TextEntry::make('correct')->label('Correct')->color('success')->badge()->state($correct),
                TextEntry::make('incorrect')->label('Incorrect')->color('danger')->badge()->state($incorrect),
                TextEntry::make('success_rate')->label('Success Rate')->html()->state($this->renderProgressBar($percentage($correct)))->columnSpanFull(),

            ]),

            Section::make('Distribution')->columns(2)->schema([
                TextEntry::make('option_1')->label(fn () => $this->optionLabel($question, 1))->html()->state($this->renderProgressBar($optionStats[1]['percentage'], $optionStats[1]['count'])),
                TextEntry::make('option_2')->label(fn () => $this->optionLabel($question, 2))->html()->state($this->renderProgressBar($optionStats[2]['percentage'], $optionStats[2]['count'])),
                TextEntry::make('option_3')->label(fn () => $this->optionLabel($question, 3))->html()->state($this->renderProgressBar($optionStats[3]['percentage'], $optionStats[3]['count'])),
                TextEntry::make('option_4')->label(fn () => $this->optionLabel($question, 4))->html()->state($this->renderProgressBar($optionStats[4]['percentage'], $optionStats[4]['count'])),
            ]),
        ];
    }

    protected function optionLabel(TheoryQuestion $question, int $option): string
    {
        return $question->answer == $option ? "Option {$option} (Correct)" : "Option {$option}";
    }

    protected function renderProgressBar(float $percentage, ?int $count = null): string
    {
        $percentage = max(0, min(100, $percentage));
        $color = $percentage >= 50 ? '#22c55e' : '#ef4444';

        $countText = $count !== null ? " ({$count})" : '';

        return "<div style='background:#ffffff; height: 10px; border-radius: 7px; width: 100%;'>
                    <div style='background:{$color}; width: {$percentage}%; height: 100%; border-radius: 7px;'></div>
                </div>
                <div style='margin-top: 6px;'>{$percentage}%{$countText}</div>";
    }
}
