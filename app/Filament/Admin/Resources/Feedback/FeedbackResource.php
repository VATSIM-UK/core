<?php

namespace App\Filament\Admin\Resources\Feedback;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Resources\Feedback\Pages\ListFeedback;
use App\Filament\Admin\Resources\Feedback\Pages\ViewFeedback;
use App\Filament\Admin\Resources\Feedback\Widgets\FeedbackOverview;
use App\Filament\Support\NameColumn;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form as FeedbackForm;
use App\Models\Mship\Qualification;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
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
use Illuminate\Database\Eloquent\Collection;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string|\UnitEnum|null $navigationGroup = 'Feedback';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withTrashed();

        return $query->with(['account', 'submitter', 'form']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('ID')
                    ->label('ID')
                    ->state(fn ($record) => $record->id),

                TextEntry::make('Form Name')
                    ->state(fn ($record) => $record->form?->name),

                TextEntry::make('account.name')
                    ->label('Subject')
                    ->state(fn ($record) => $record->account?->name),

                TextEntry::make('accountAtcQualification.name')
                    ->label('Subject\'s ATC Qualification')
                    ->state(fn ($record) => $record->accountAtcQualification?->name ?? 'Not Found'),

                TextEntry::make('submitter.name')
                    ->label('Submitted by')
                    ->visible(self::canSeeSubmitter())
                    ->state(fn ($record) => $record->submitter?->name),

                TextEntry::make('created_at')
                    ->label('Submitted at')
                    ->state(fn ($record) => $record->created_at->format('d/m/Y H:i')),

                Fieldset::make('Sent Information')->columnSpanFull()
                    ->schema([
                        TextEntry::make('sent_at')
                            ->label('Sent At')
                            ->state(fn ($record) => $record->sent_at ? $record->sent_at->format('d/m/Y H:i') : null),

                        TextEntry::make('sent_by')
                            ->label('Sent By')
                            ->state(fn ($record) => $record->sent_by_id ? $record->sender?->name : null),

                        TextEntry::make('sent_comment')
                            ->label('Sent Notes')
                            ->state(fn ($record) => $record->sent_comment),
                    ])->hidden(fn ($record) => $record->sent_at === null),

                Fieldset::make('Actioned Information')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('actioned_at')
                            ->label('Actioned At')
                            ->state(fn ($record) => $record->actioned_at ? $record->actioned_at->format('d/m/Y H:i') : null),

                        TextEntry::make('actioned_by')
                            ->label('Actioned By')
                            ->state(fn ($record) => $record->actioned_by_id ? $record->actioner?->name : null),

                        TextEntry::make('actioned_comment')
                            ->label('Actioned Comment')
                            ->state(fn ($record) => $record->actioned_comment),
                    ])->hidden(fn ($record) => $record->actioned_at === null),

                Fieldset::make('Rejection Information')
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('rejected_by')
                            ->label('Rejected By')
                            ->state(fn ($record) => $record->deleted_by ? $record->deleter?->name : null),

                        TextEntry::make('reject_reason')
                            ->label('Rejection Reason')
                            ->state(fn ($record) => $record->reject_reason),
                    ])->hidden(fn ($record) => ! $record->trashed()),

                Section::make('Answers')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('Answers')
                            ->relationship('answers')
                            ->label('')
                            ->schema([
                                TextEntry::make('question')
                                    ->label('Question')
                                    ->state(fn ($record) => $record->question?->question),

                                TextEntry::make('response')
                                    ->label('Answer')
                                    ->extraAttributes(['style' => 'white-space: pre-line;'])
                                    ->state(fn ($record) => $record->response),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.name')->label('Feedback Type')
                    ->sortable(),
                NameColumn::make('account.name')->label('Subject'),
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

                SelectFilter::make('account_atc_qualification_id')
                    ->placeholder('All ATC Qualifications')
                    ->label('Subject ATC Qualification')
                    ->options(
                        Qualification::whereType('atc')->get()->mapWithKeys(fn ($qualification) => [$qualification->id => $qualification->name])
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::actionFeedbackBulkAction(),
                    self::sendFeedbackBulkAction(),
                    self::reallocateFeedbackBulkAction(),
                    self::rejectFeedbackBulkAction(),
                ]),
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

    private static function configureBulkAction(BulkAction $action): BulkAction
    {
        return $action
            ->visible(fn () => auth()->user()->can('actionFeedback', Feedback::class))
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion();
    }

    private static function actionFeedbackBulkAction(): BulkAction
    {
        return self::configureBulkAction(
            BulkAction::make('action_feedback')
                ->label('Action Feedback')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->schema([self::commentField()])
                ->action(function (Collection $records, array $data) {
                    self::processBulkAction($records, 'actioned at', 'actioned_at', fn ($record) => $record->markActioned(auth()->user(), $data['comment']));
                })
        );
    }

    private static function sendFeedbackBulkAction(): BulkAction
    {
        return self::configureBulkAction(
            BulkAction::make('send_feedback')
                ->label('Send Feedback to Member')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->schema([self::commentField()])
                ->action(function (Collection $records, array $data) {
                    self::processBulkAction($records, 'sent to member', 'sent_at', fn ($record) => $record->markSent(auth()->user(), $data['comment']));
                })
        );
    }

    private static function reallocateFeedbackBulkAction(): BulkAction
    {
        return self::configureBulkAction(
            BulkAction::make('reallocate_feedback')
                ->label('Reallocate Feedback')
                ->icon('heroicon-o-arrow-right')
                ->color('gray')
                ->modalSubmitActionLabel('Reallocate')
                ->schema([
                    AccountSelect::make('account')
                        ->label('Account')
                        ->helperText('Select the account you want to reallocate the feedback to.')
                        ->required(),
                ])
                ->action(function (Collection $records, array $data) {
                    $count = $records
                        ->filter(fn ($record) => $record->actioned_at === null && $record->sent_at === null)
                        ->each(fn ($record) => $record->reallocate($data['account_id']))
                        ->count();

                    Notification::make()
                        ->title("{$count} feedback record(s) reallocated successfully.")
                        ->success()
                        ->send();
                })
        );
    }

    private static function rejectFeedbackBulkAction(): BulkAction
    {
        return self::configureBulkAction(
            BulkAction::make('reject_feedback')
                ->label('Reject Feedback')
                ->icon('heroicon-o-x-mark')
                ->color('danger')
                ->schema([self::reasonField()])
                ->action(function (Collection $records, array $data) {
                    $count = $records
                        ->filter(fn ($record) => ! $record->trashed())
                        ->each(fn ($record) => $record->markRejected(auth()->user(), $data['reason']))
                        ->count();

                    Notification::make()
                        ->title("{$count} feedback record(s) rejected successfully.")
                        ->success()
                        ->send();
                })
        );
    }

    public static function commentField(): Textarea
    {
        return Textarea::make('comment')
            ->label('Comment')
            ->rules('required', 'min:10');
    }

    public static function reasonField(): Textarea
    {
        return Textarea::make('reason')
            ->label('Rejection Reason')
            ->rules('required', 'min:10');
    }

    private static function processBulkAction(Collection $records, string $successLabel, string $nullCheckColumn, callable $processor): void
    {
        $count = $records
            ->filter(fn ($record) => $record->{$nullCheckColumn} === null)
            ->each($processor)
            ->count();

        Notification::make()
            ->title("{$count} feedback record(s) {$successLabel} successfully.")
            ->success()
            ->send();
    }
}
