<?php

namespace App\Filament\Resources\TheoryManagementResource\RelationManagers;

use App\Models\Cts\TheoryQuestion;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class TheoryQuestionsManager extends RelationManager
{
    protected static string $relationship = 'questions';

    public static function canViewForRecord($ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('theory-exams.questions.view.*');
    }

    protected function canViewTable(): bool
    {
        return auth()->user()->can('theory-exams.questions.view.*');
    }

    protected function canCreate(): bool
    {
        return auth()->user()->can('theory-exams.questions.create.*');
    }

    protected function canEdit($record): bool
    {
        return auth()->user()->can('theory-exams.questions.edit.*');
    }

    protected function canDelete($record): bool
    {
        return auth()->user()->can('theory-exams.questions.delete.*');
    }

    protected function getTableQuery(): Builder
    {
        $level = str_replace('theory_', '', $this->ownerRecord->item);

        return TheoryQuestion::query()->where('level', $level);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->searchable(),
                TextColumn::make('question')->searchable(),
                TextColumn::make('deleted')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state == 0 ? 'Enabled' : 'Disabled')
                    ->color((fn ($state) => $state == 0 ? 'success' : 'danger'))
                    ->toggleable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form($this->getQuestionFormSchema()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->form($this->getQuestionFormSchema()),

                Tables\Actions\EditAction::make()
                    ->form($this->getQuestionFormSchema()),

                Tables\Actions\DeleteAction::make(),
            ]);
    }

    protected function getQuestionFormSchema(): array
    {
        return [
            Section::make('Question Details')
                ->schema([
                    TextInput::make('question')->required()->maxlength(255),
                    Grid::make(2)->schema([
                        TextInput::make('option_1')->required()->maxlength(255)->label('Option 1'),
                        TextInput::make('option_2')->required()->maxlength(255)->label('Option 2'),
                        TextInput::make('option_3')->required()->maxlength(255)->label('Option 3'),
                        TextInput::make('option_4')->required()->maxlength(255)->label('Option 4'),
                        Select::make('answer')
                            ->options([
                                '1' => 'Option 1',
                                '2' => 'Option 2',
                                '3' => 'Option 3',
                                '4' => 'Option 4',
                            ])->label('Correct Answer')->required(),
                        Toggle::make('deleted')->label('Enabled')
                            ->onColor('success')
                            ->offColor('danger')
                            ->inline(false)
                            ->afterStateHydrated(function ($component, $state) {
                                $component->state($state == 0);
                            })->dehydrateStateUsing(fn ($state) => $state ? 0 : 1),
                    ]),
                ]),

            // Display only fields
            Section::make('Additional Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('add_by')->label('Added By')->disabled(),
                        DatePicker::make('add_date')->label('Added Date')->disabled(),
                        TextInput::make('edit_by')->label('Edited By')->disabled(),
                        DatePicker::make('edit_date')->label('Edited Date')->disabled(),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),

        ];
    }
}
