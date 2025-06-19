<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TheoryResultResource\Pages;
use App\Models\Cts\TheoryResult;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TheoryResultResource extends Resource
{
    protected static ?string $model = TheoryResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Mentoring';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('member_information')
                    ->label('Member Information')
                    ->schema([
                        Forms\Components\Placeholder::make('student_id')
                            ->label('CID')
                            ->content(fn ($record) => $record->student_id),

                        Forms\Components\Placeholder::make('name')
                            ->label('Name')
                            ->content(fn ($record) => $record->name),
                    ]),

                Forms\Components\Fieldset::make('exam_information')
                    ->label('Exam Information')
                    ->schema([
                        Forms\Components\Placeholder::make('exam')
                            ->label('Exam')
                            ->content(fn ($record) => $record->exam),

                        Forms\Components\Placeholder::make('correct_questions')
                            ->label('Correct / Total Questions')
                            ->content(fn ($record) => "{$record->correct} out of {$record->questions}"),

                        Forms\Components\Placeholder::make('time_mins')
                            ->label('Time (mins)')
                            ->content(fn ($record) => $record->time_mins),

                        /* Forms\Components\Placeholder::make('correct')
                            ->label('Correct Answers')
                            ->content(fn ($record) => $record->correct), */

                        Forms\Components\Placeholder::make('passmark')
                            ->label('Passmark')
                            ->content(fn ($record) => $record->passmark),

                        Forms\Components\Placeholder::make('started')
                            ->label('Started')
                            ->content(fn ($record) => $record->started),

                        Forms\Components\Placeholder::make('expires')
                            ->label('Expires')
                            ->content(fn ($record) => $record->expires),

                        Forms\Components\Placeholder::make('submitted_time')
                            ->label('Submitted')
                            ->content(fn ($record) => $record->submitted_time),

                        Forms\Components\Placeholder::make('pass_status')
                            ->label('Result')
                            ->content(fn ($record) => $record->correct >= $record->passmark ? 'Passed' : 'Failed'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // TextColumn::make('id')->label('ID')->sortable()->searchable(),
                TextColumn::make('exam')->label('Exam')->sortable()->searchable(),
                TextColumn::make('student_id')->label('CID')->sortable()->searchable(),
                TextColumn::make('name')->label('Name')->sortable()->searchable(),
                // TextColumn::make('questions')->label('CID')->sortable()->searchable(),
                // TextColumn::make('time_mins')->label('Time (mins)')->sortable(),
                // TextColumn::make('correct')->label('Correct')->sortable(),
                // TextColumn::make('passmark')->label('Passmark')->sortable(),
                TextColumn::make('pass_status')
                    ->label('Result')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->correct >= $record->passmark ? 'Passed' : 'Failed')
                    ->color(fn ($record) => match ($record->correct >= $record->passmark) {
                        true => 'success',
                        false => 'danger',
                        default => 'warning',
                    })->sortable(),
            ])
            ->filters([
                /*Tables\Filters\SelectFilter::make('exam')
                ->label('Exam')
                ->options(fn () => self::$model::query()->distinct()->pluck('exam', 'exam')->toArray()),*/
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTheoryResults::route('/'),
            'create' => Pages\CreateTheoryResult::route('/create'),
            'edit' => Pages\EditTheoryResult::route('/{record}/edit'),
        ];
    }
}
