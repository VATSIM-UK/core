<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Filament\Resources\FeedbackResource\RelationManagers;
use App\Models\Mship\Feedback\Feedback;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make("ID")
                    ->label("ID")
                    ->content(fn ($record) => $record->id),

                Forms\Components\Placeholder::make("Form Name")
                    ->content(fn ($record) => $record->form->name),

                Forms\Components\Placeholder::make('account.name')
                    ->label('Subject')
                    ->content(fn ($record) => $record->account->name),

                Forms\Components\Placeholder::make('submitter.name')
                    ->label('Submitted by')
                    ->hidden(fn ($record) => !auth()->user()->can('feedback.view-sensitive', $record)),

                Forms\Components\Placeholder::make('created_at')
                    ->label('Submitted at')
                    ->content(fn ($record) => $record->created_at->format('d/m/Y H:i')),

                Forms\Components\Fieldset::make("Sent Information")
                    ->schema([
                        Forms\Components\Placeholder::make('sent_at')
                            ->label('Sent At')
                            ->content(fn ($record) => $record->sent_at ? $record->sent_at->format('d/m/Y H:i') : null),

                        Forms\Components\Placeholder::make('sent_by')
                            ->label('Sent By')
                            ->content(fn ($record) => $record->sent_by ? $record->sent_by->name : null),

                        Forms\Components\Placeholder::make('sent_notes')
                            ->label('Sent Notes')
                            ->content(fn ($record) => $record->sent_notes),
                    ])->hidden(fn ($record) => $record->sent_at === null),

                Forms\Components\Fieldset::make("Actioned Information")
                    ->schema([
                        Forms\Components\Placeholder::make('actioned_at')
                            ->label('Actioned At')
                            ->content(fn ($record) => $record->actioned_at ? $record->actioned_at->format('d/m/Y H:i') : null),

                        Forms\Components\Placeholder::make('actioned_by')
                            ->label('Actioned By')
                            ->content(fn ($record) => $record->actioned_by_id ? $record->actioner->name : null),

                        Forms\Components\Placeholder::make('actioned_comment')
                            ->label('Actioned Comment')
                            ->content(fn ($record) => $record->actioned_comment),
                    ])->hidden(fn ($record) => $record->actioned_at === null),

                Forms\Components\Section::make("Answers")
                    ->schema([
                        Forms\Components\Repeater::make("Answers")
                            ->relationship('answers')
                            ->label("")
                            ->schema([
                                Forms\Components\Placeholder::make('question')
                                    ->label('Question')
                                    ->content(fn ($record) => $record->question->question),

                                Forms\Components\Placeholder::make('response')
                                    ->label('Answer')
                                    ->content(fn ($record) => $record->response),
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('account.name')->label('Subject'),
                Tables\Columns\TextColumn::make('submitter.name')->label('Submitted By')->hidden(fn ($record) => !auth()->user()->can('feedback.view-sensitive', $record)),
                Tables\Columns\TextColumn::make('created_at')
                	->dateTime("d/m/Y H:i")
                    ->sortable(),
                Tables\Columns\IconColumn::make('actioned_at')
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Actioned')
                    ->trueColor('green')
                    ->falseColor('red')
                    ->getStateUsing(fn($record) => $record->actioned_at !== null),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'view' => Pages\ViewFeedback::route('/{record}'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
