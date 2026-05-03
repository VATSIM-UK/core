<?php

namespace App\Filament\Admin\Resources\Feedback;

use App\Filament\Admin\Resources\Feedback\Pages\ListFeedback;
use App\Filament\Admin\Resources\Feedback\Pages\ViewFeedback;
use App\Filament\Admin\Resources\Feedback\Widgets\FeedbackOverview;
use App\Models\Mship\Account;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Mship\Feedback\Form as FeedbackForm;
use App\Models\Mship\Qualification;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
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
                                    ->state(fn ($record) => $record->response)
                                    ->copyable(),
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
                SelectFilter::make('account_id')
                    ->placeholder('All Subjects')
                    ->label('Subject')
                    ->searchable(['name_first', 'name_last', 'id'])
                    ->options(
                        Feedback::query()->with('account')->get()->mapWithKeys(fn ($feedback) => [
                            $feedback->account->id => $feedback->account->name.' ('.$feedback->account->id.')',
                        ])
                    ),
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
            ->selectable()
            ->toolbarActions([
                BulkAction::make('sendFeedback')
                    ->label(static::sendFeedbackConfig()['label'])
                    ->icon(static::sendFeedbackConfig()['icon'])
                    ->color(static::sendFeedbackConfig()['color'])
                    ->schema(static::sendFeedbackConfig()['form'])
                    ->visible(fn () => auth()->user()?->can('actionFeedback', self::getModel()))
                    ->action(function ($records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->sent_at === null && ! $record->trashed()) {
                                $record->markSent(auth()->user(), $data['comment']);
                                $count++;
                            }
                        }
                        Notification::make()
                            ->title("{$count} feedback entries sent.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('actionFeedback')
                    ->label(static::actionFeedbackConfig()['label'])
                    ->icon(static::actionFeedbackConfig()['icon'])
                    ->color(static::actionFeedbackConfig()['color'])
                    ->schema(static::actionFeedbackConfig()['form'])
                    ->visible(fn () => auth()->user()?->can('actionFeedback', self::getModel()))
                    ->action(function ($records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->actioned_at === null && ! $record->trashed()) {
                                $record->markActioned(auth()->user(), $data['comment']);
                                $count++;
                            }
                        }
                        Notification::make()
                            ->title("{$count} feedback entries actioned.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('rejectFeedback')
                    ->label(static::rejectFeedbackConfig()['label'])
                    ->icon(static::rejectFeedbackConfig()['icon'])
                    ->color(static::rejectFeedbackConfig()['color'])
                    ->schema(static::rejectFeedbackConfig()['form'])
                    ->visible(fn () => auth()->user()?->can('actionFeedback', self::getModel()))
                    ->action(function ($records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (! $record->trashed()) {
                                $record->markRejected(auth()->user(), $data['reason']);
                                $count++;
                            }
                        }
                        Notification::make()
                            ->title("{$count} feedback entries rejected.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                BulkAction::make('reallocateFeedback')
                    ->label(static::reallocateFeedbackConfig()['label'])
                    ->icon(static::reallocateFeedbackConfig()['icon'])
                    ->color(static::reallocateFeedbackConfig()['color'])
                    ->schema(static::reallocateFeedbackConfig()['form'])
                    ->visible(fn () => auth()->user()?->can('actionFeedback', self::getModel()))
                    ->action(function ($records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->actioned_at === null && $record->sent_at === null && ! $record->trashed()) {
                                $record->reallocate($data['account_id']);
                                $count++;
                            }
                        }
                        Notification::make()
                            ->title("{$count} feedback entries reallocated.")
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                DeleteBulkAction::make()
                    ->visible(fn () => auth()->user()?->can('delete', self::getModel())),
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

    public static function sendFeedbackConfig(): array
    {
        return [
            'label' => 'Send Feedback',
            'icon' => 'heroicon-o-paper-airplane',
            'color' => 'success',
            'form' => [
                Textarea::make('comment')
                    ->label('Comment')
                    ->required()
                    ->minLength(10),
            ],
        ];
    }

    public static function actionFeedbackConfig(): array
    {
        return [
            'label' => 'Action Feedback',
            'icon' => 'heroicon-o-check-circle',
            'color' => 'info',
            'form' => [
                Textarea::make('comment')
                    ->label('Comment')
                    ->required()
                    ->minLength(10),
            ],
        ];
    }

    public static function rejectFeedbackConfig(): array
    {
        return [
            'label' => 'Reject Feedback',
            'icon' => 'heroicon-o-x-mark',
            'color' => 'danger',
            'form' => [
                Textarea::make('reason')
                    ->label('Rejection Reason')
                    ->required()
                    ->minLength(10),
            ],
        ];
    }

    public static function reallocateFeedbackConfig(): array
    {
        return [
            'label' => 'Reallocate Feedback',
            'icon' => 'heroicon-o-arrow-right',
            'color' => 'gray',
            'form' => [
                Select::make('account_id')
                    ->label('New Subject')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => Account::query()
                        ->where('name_first', 'like', "%{$search}%")
                        ->orWhere('name_last', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn ($account) => [$account->id => $account->name.' ('.$account->id.')'])
                        ->toArray())
                    ->getOptionLabelUsing(fn ($value): ?string => Account::find($value)?->name.' ('.$value.')')
                    ->required(),
            ],
        ];
    }
}
