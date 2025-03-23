<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Filament\Resources\FeedbackResource\Widgets\FeedbackOverview;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form as FeedbackForm;
use AxonC\FilamentCopyablePlaceholder\Forms\Components\CopyablePlaceholder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Feedback';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->with(['account', 'submitter', 'form']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('ID')
                    ->label('ID')
                    ->content(fn ($record) => $record->id),

                Forms\Components\Placeholder::make('Form Name')
                    ->content(fn ($record) => $record->form->name),

                Forms\Components\Placeholder::make('account.name')
                    ->label('Subject')
                    ->content(fn ($record) => $record->account->name),

                Forms\Components\Placeholder::make('submitter.name')
                    ->label('Submitted by')
                    ->visible(self::canSeeSubmitter())
                    ->content(fn ($record) => $record->submitter->name),

                Forms\Components\Placeholder::make('created_at')
                    ->label('Submitted at')
                    ->content(fn ($record) => $record->created_at->format('d/m/Y H:i')),

                Forms\Components\Fieldset::make('Sent Information')
                    ->schema([
                        Forms\Components\Placeholder::make('sent_at')
                            ->label('Sent At')
                            ->content(fn ($record) => $record->sent_at ? $record->sent_at->format('d/m/Y H:i') : null),

                        Forms\Components\Placeholder::make('sent_by')
                            ->label('Sent By')
                            ->content(fn ($record) => $record->sent_by_id ? $record->sender->name : null),

                        Forms\Components\Placeholder::make('sent_comment')
                            ->label('Sent Notes')
                            ->content(fn ($record) => $record->sent_comment),
                    ])->hidden(fn ($record) => $record->sent_at === null),

                Forms\Components\Fieldset::make('Actioned Information')
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

                Forms\Components\Section::make('Answers')
                    ->schema([
                        Forms\Components\Repeater::make('Answers')
                            ->relationship('answers')
                            ->label('')
                            ->schema([
                                Forms\Components\Placeholder::make('question')
                                    ->label('Question')
                                    ->content(fn ($record) => $record->question->question),

                                CopyablePlaceholder::make('response')
                                    ->label('Answer')
                                    // FIXME: Tailwind classes defined in filament php files aren't being added to stylesheet.
                                    ->extraAttributes(['style' => 'white-space: pre-line;'])
                                    ->content(fn ($record) => $record->response)
                                    ->iconOnly(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('account.name')->label('Subject')->searchable(['name_first', 'name_last']),
                Tables\Columns\TextColumn::make('submitter.name')->label('Submitted By')->visible(self::canSeeSubmitter()),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\IconColumn::make('actioned_at')
                    ->timestampBoolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Actioned'),
                Tables\Columns\IconColumn::make('sent_at')
                    ->timestampBoolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Sent to User'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('form')
                    ->placeholder('All Forms')
                    ->label('Feedback Form')
                    ->options(
                        FeedbackForm::all()->mapWithKeys(fn ($form) => [$form->id => $form->name])
                    )
                    ->attribute('form_id'),
                Tables\Filters\TernaryFilter::make('actioned')
                    ->placeholder('All')
                    ->options([
                        'Actioned' => true,
                        'Un-actioned' => false,
                    ])
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('actioned_at'),
                        false: fn ($query) => $query->whereNull('actioned_at'),
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeedback::route('/'),
            'view' => Pages\ViewFeedback::route('/{record}'),
        ];
    }

    public static function getWidgets(): array
    {
        return [
            FeedbackOverview::class,
        ];
    }

    private static function canSeeSubmitter()
    {
        return auth()->user()->can('seeSubmitter', self::getModel());
    }
}
