<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FeedbackResource\Pages\ListFeedback;
use App\Filament\Admin\Resources\FeedbackResource\Pages\ViewFeedback;
use App\Filament\Admin\Resources\FeedbackResource\Widgets\FeedbackOverview;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form as FeedbackForm;
use AxonC\FilamentCopyablePlaceholder\Forms\Components\CopyablePlaceholder;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Feedback';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->with(['account', 'submitter', 'form']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('ID')
                    ->label('ID')
                    ->content(fn ($record) => $record->id),

                Placeholder::make('Form Name')
                    ->content(fn ($record) => $record->form->name),

                Placeholder::make('account.name')
                    ->label('Subject')
                    ->content(fn ($record) => $record->account->name),

                Placeholder::make('submitter.name')
                    ->label('Submitted by')
                    ->visible(self::canSeeSubmitter())
                    ->content(fn ($record) => $record->submitter->name),

                Placeholder::make('created_at')
                    ->label('Submitted at')
                    ->content(fn ($record) => $record->created_at->format('d/m/Y H:i')),

                Fieldset::make('Sent Information')
                    ->schema([
                        Placeholder::make('sent_at')
                            ->label('Sent At')
                            ->content(fn ($record) => $record->sent_at ? $record->sent_at->format('d/m/Y H:i') : null),

                        Placeholder::make('sent_by')
                            ->label('Sent By')
                            ->content(fn ($record) => $record->sent_by_id ? $record->sender->name : null),

                        Placeholder::make('sent_comment')
                            ->label('Sent Notes')
                            ->content(fn ($record) => $record->sent_comment),
                    ])->hidden(fn ($record) => $record->sent_at === null),

                Fieldset::make('Actioned Information')
                    ->schema([
                        Placeholder::make('actioned_at')
                            ->label('Actioned At')
                            ->content(fn ($record) => $record->actioned_at ? $record->actioned_at->format('d/m/Y H:i') : null),

                        Placeholder::make('actioned_by')
                            ->label('Actioned By')
                            ->content(fn ($record) => $record->actioned_by_id ? $record->actioner->name : null),

                        Placeholder::make('actioned_comment')
                            ->label('Actioned Comment')
                            ->content(fn ($record) => $record->actioned_comment),
                    ])->hidden(fn ($record) => $record->actioned_at === null),

                Section::make('Answers')
                    ->schema([
                        Repeater::make('Answers')
                            ->relationship('answers')
                            ->label('')
                            ->schema([
                                Placeholder::make('question')
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
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('form.name')->label('Feedback Type')
                    ->sortable(),
                TextColumn::make('account.name')->label('Subject')->searchable(['name_first', 'name_last']),
                TextColumn::make('submitter.name')->label('Submitted By')->visible(self::canSeeSubmitter()),
                TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                IconColumn::make('actioned_at')
                    ->timestampBoolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Actioned'),
                IconColumn::make('sent_at')
                    ->timestampBoolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-s-x-circle')
                    ->label('Sent to User'),
            ])
            ->filters([
                SelectFilter::make('form')
                    ->placeholder('All Forms')
                    ->label('Feedback Form')
                    ->options(
                        FeedbackForm::all()->mapWithKeys(fn ($form) => [$form->id => $form->name])
                    )
                    ->attribute('form_id'),
                TernaryFilter::make('actioned')
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
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedback::route('/'),
            'view' => ViewFeedback::route('/{record}'),
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
