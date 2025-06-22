<?php

namespace App\Filament\Resources\TheoryManagementResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;

class TheoryQuestionsManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected function getTableQuery(): Builder
    {
        return $this->ownerRecord->questions()->getQuery()->where('deleted', 0);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('question')->searchable(),
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
                        Toggle::make('status')->label('Enabled')->onColor('success')->offColor('danger')->inline(false),
                    ]),
                ]),

            // Display only fields
            Section::make('Additional Details')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('add_by')->required()->label('Added By')->disabled(),
                        DatePicker::make('add_date')->required()->label('Added Date')->disabled(),
                        TextInput::make('edit_by')->required()->label('Edited By')->disabled(),
                        DatePicker::make('edit_date')->required()->label('Edited Date')->disabled(),
                    ]),
                ])
                ->collapsible()
                ->collapsed(),

        ];
    }
}
