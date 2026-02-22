<?php

namespace App\Filament\Training\Pages\TheoryExam;

use App\Models\Cts\TheoryQuestion;
use Carbon\Carbon;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class TheoryExamQuestions extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static string $view = 'filament.training.pages.theory-exam-questions';

    protected static ?string $navigationGroup = 'Theory';

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

        $this->level = request()->get('level', $this->allowedLevels[0] ?? 'S1'); // default to first allowed level
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
                return \Filament\Actions\Action::make($level)
                    ->label($level)
                    ->url(fn () => static::getUrl(['level' => $level]))
                    ->color($this->level === $level ? 'primary' : 'gray');
            })->toArray();

        $createButton = \Filament\Actions\CreateAction::make('create')
            ->label('Create Question')
            ->form($this->getQuestionFormSchema())
            ->color('success')
            ->using(function (array $data, $action) {
                TheoryQuestion::create([
                    ...$data,
                    'add_by' => auth()->id(),
                    'add_date' => now(),
                    'edit_by' => auth()->id(),
                    'edit_date' => now(),
                ]);

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

    public function table(Table $table): Table
    {

        $query = TheoryQuestion::query()->where('deleted', 0)->where('level', $this->level)->whereIn('level', $this->allowedLevels);

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('id')->toggleable(isToggledHiddenByDefault: true)->searchable(),
                TextColumn::make('question')->wrap()->searchable(),
                IconColumn::make('status')
                    ->boolean()
                    ->label('Active')->sortable(),
            ])
            ->actions([
                Action::make('edit')
                    ->form($this->getQuestionFormSchema())
                    ->icon('heroicon-o-pencil')
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
            ])
            ->paginated(['10', '25', '50'])
            ->defaultPaginationPageOption(25);
    }

    protected function getQuestionFormSchema(): array
    {
        return [
            Select::make('level')
                ->options(fn () => collect($this->allowedLevels)->mapWithKeys(fn ($level) => [$level => $level])->toArray())->default($this->level)
                ->required(),
            Textarea::make('question')->rows(2)->autosize()->required(),
            TextInput::make('option_1')->required(),
            TextInput::make('option_2')->required(),
            TextInput::make('option_3')->required(),
            TextInput::make('option_4')->required(),
            Select::make('answer')
                ->options([
                    1 => 'Option 1',
                    2 => 'Option 2',
                    3 => 'Option 3',
                    4 => 'Option 4',
                ])->required(),

            Toggle::make('status')
                ->label('Active')
                ->required(),

            Section::make('Additional Information')
                ->collapsed()
                ->columns(2)
                ->schema([
                    TextInput::make('added_by')->disabled(),
                    TextInput::make('added_date')->disabled(),
                    TextInput::make('edited_by')->disabled(),
                    TextInput::make('edited_date')->disabled(),
                ]),
        ];
    }
}
