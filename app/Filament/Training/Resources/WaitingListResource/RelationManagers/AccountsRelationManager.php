<?php

namespace App\Filament\Training\Resources\WaitingListResource\RelationManagers;

use App\Enums\TrainingPlaceOfferStatus;
use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Services\Training\TrainingPlaceOfferService;
use App\Services\Training\TrainingPlaceService;
use AxonC\FilamentCopyablePlaceholder\Forms\Components\CopyablePlaceholder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

/**
 * @property WaitingList $ownerRecord
 */
class AccountsRelationManager extends RelationManager
{
    protected static string $relationship = 'waitingListAccounts';

    protected $listeners = ['refreshWaitingList' => '$refresh'];

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('base_information')
                    ->label('Base Information')
                    ->schema([
                        CopyablePlaceholder::make('id')
                            ->label('CID')
                            ->content(fn (WaitingListAccount $record) => $record->account_id)
                            ->iconOnly(),

                        CopyablePlaceholder::make('name')
                            ->label('Name')
                            ->content(fn (WaitingListAccount $record) => $record->account->name)
                            ->iconOnly(),

                        Forms\Components\Placeholder::make('position')
                            ->label('Position')
                            ->content(function (WaitingListAccount $record) {
                                return sprintf(
                                    '%s of %d',
                                    $this->ownerRecord->positionOf($record) ?? '-',
                                    $this->ownerRecord->waitingListAccounts->count()
                                );
                            }),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Add notes here'),

                    ]),

                Forms\Components\Fieldset::make('cts_theory_exam')
                    ->label('CTS Theory Exam')
                    ->schema(function (WaitingListAccount $record) {
                        return [
                            Forms\Components\Toggle::make('cts_theory_exam')
                                ->label('Passed')
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $record->theory_exam_passed))
                                ->disabled(),
                        ];
                    })
                    ->visible(fn ($record) => $record->waitingList->feature_toggles['check_cts_theory_exam'] ?? true),

                Forms\Components\Fieldset::make('manual_flags')
                    ->label('Manual Flags')
                    ->schema(function (WaitingListAccount $record) {
                        return $record->flags->filter(fn ($flag) => $flag->position_group_id == null)->map(function ($flag) {
                            return Forms\Components\Toggle::make('flags.'.$flag->id)
                                ->label($flag->name)
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $flag->pivot->value));
                        })->all();
                    })
                    ->visible(fn (WaitingListAccount $record) => $record->flags->isNotEmpty()),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['account', 'account.roster', 'waitingList', 'flags']))
            ->columns([
                Tables\Columns\TextColumn::make('position')->getStateUsing(fn (WaitingListAccount $record) => $this->ownerRecord->positionOf($record) ?? '-')->label('Position'),
                Tables\Columns\TextColumn::make('account_id')->label('CID')->searchable(),
                Tables\Columns\TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
                Tables\Columns\IconColumn::make('on_roster')->boolean()->label('On Roster')->getStateUsing(fn (WaitingListAccount $record) => $record->account->onRoster())->visible(fn () => $this->ownerRecord->feature_toggles['display_on_roster'] ?? true),
                Tables\Columns\TextColumn::make('created_at')->label('Added On')->dateTime('d/m/Y H:i:s'),
                Tables\Columns\IconColumn::make('cts_theory_exam')->boolean()->label('CTS Theory Exam')->getStateUsing(fn (WaitingListAccount $record) => $record->theory_exam_passed)->visible(fn () => $this->ownerRecord->feature_toggles['check_cts_theory_exam'] ?? true),
                ...$this->getFlagColumns(),
            ])
            ->actions([
                Tables\Actions\Action::make('offerTrainingPlace')
                    ->label('Offer Training Place')
                    ->icon('heroicon-o-academic-cap')
                    ->visible(function (WaitingListAccount $record) {
                        return $this->can('offerTrainingPlace', $record->waitingList) && ! $record->hasPendingTrainingPlaceOffer();
                    })
                    ->form(function (WaitingListAccount $record) {
                        $recentFeedback = Feedback::where('account_id', $record->account_id)
                            ->with(['answers.question'])
                            ->latest()
                            ->limit(10)
                            ->get();

                        $feedbackEntries = $recentFeedback->map(fn (Feedback $feedback) => Forms\Components\Section::make("Feedback - {$feedback->created_at->format('d/m/Y H:i')}")
                            ->schema([
                                ...$feedback->answers->map(fn ($answer) => Forms\Components\Placeholder::make("answer_{$answer->id}")
                                    ->label($answer->question?->question ?? 'Unknown Question')
                                    ->content($answer->response ?? 'Question not answered')
                                )->all(),
                            ])
                            ->columns(3)
                            ->collapsible()
                        )->all();

                        return [
                            Forms\Components\Section::make('Member Feedback')
                                ->schema($feedbackEntries ?: [
                                    Forms\Components\Placeholder::make('no_feedback')
                                        ->label('')
                                        ->content('No feedback on record for this member.'),
                                ]
                                )
                                ->collapsible()
                                ->columns(3),

                            Forms\Components\Select::make('training_position_id')
                                ->label('Training Position')
                                ->options(function ($livewire) {
                                    return $livewire->ownerRecord->trainingPositions
                                        ->mapWithKeys(fn ($tp) => [$tp->id => $tp->position?->callsign ?? "Position #{$tp->id}"])
                                        ->toArray();
                                })
                                ->required()
                                ->helperText('Select the training position to offer to this member.'),
                        ];
                    })
                    ->action(function (WaitingListAccount $record, array $data, $livewire) {
                        $trainingPosition = $livewire->ownerRecord->trainingPositions()->findOrFail($data['training_position_id']);

                        $service = app(TrainingPlaceOfferService::class);
                        $service->offerTrainingPlace($record, $trainingPosition);
                    })
                    ->successNotificationTitle('Training place offered successfully')
                    ->modalHeading('Offer Training Place')
                    ->modalDescription('Select a training position to offer this member.')
                    ->modalSubmitActionLabel('Offer Training Place')
                    ->modalCancelActionLabel('Cancel')
                    ->color('success'),

                Tables\Actions\ViewAction::make()
                    ->modalHeading(fn (?WaitingListAccount $record) => "Waiting List Account — {$record?->account->name}")
                    ->extraModalFooterActions(function (?WaitingListAccount $record) {
                        if (! $record) {
                            return [];
                        }

                        $offer = $record->trainingPlaceOffers()
                            ->where('status', TrainingPlaceOfferStatus::Pending->value)
                            ->latest()
                            ->first();

                        if (! $offer) {
                            return [];
                        }

                        return [
                            Tables\Actions\Action::make('rescind')
                                ->label('Rescind Offer')
                                ->color('danger')
                                ->icon('heroicon-o-x-circle')
                                ->visible(fn ($record) => $this->can('rescindTrainingPlaceOffer', $record->waitingList))
                                ->modalHeading('Rescind Training Place Offer')
                                ->modalDescription('The member will be notified. Their waiting list position will be retained.')
                                ->modalSubmitActionLabel('Rescind Offer')
                                ->form([
                                    Forms\Components\Textarea::make('reason')
                                        ->label('Reason for rescinding')
                                        ->placeholder('Please provide a reason, this will be included in the email to the member.')
                                        ->required()
                                        ->minLength(10)
                                        ->rows(4),
                                ])
                                ->action(function (array $data) use ($offer) {
                                    $service = app(TrainingPlaceOfferService::class);
                                    $service->rescindOffer($offer, $data['reason']);
                                })
                                ->successNotificationTitle('Offer rescinded'),

                            Tables\Actions\Action::make('rescindAndRemove')
                                ->label('Rescind Offer & Remove')
                                ->color('danger')
                                ->icon('heroicon-o-trash')
                                ->visible(fn ($record) => $this->can('rescindTrainingPlaceOffer', $record->waitingList) && $this->can('removeAccount', $record->waitingList))
                                ->modalHeading('Rescind Offer & Remove from Waiting List')
                                ->modalDescription('The member will be removed from the waiting list entirely. This cannot be undone.')
                                ->modalSubmitActionLabel('Rescind & Remove')
                                ->form([
                                    Forms\Components\Textarea::make('reason')
                                        ->label('Reason for rescinding')
                                        ->placeholder('Please provide a reason, this will be included in the email to the member.')
                                        ->required()
                                        ->minLength(10)
                                        ->rows(4),
                                ])
                                ->action(function (array $data, $livewire) use ($offer) {
                                    $service = app(TrainingPlaceOfferService::class);
                                    $service->rescindOfferAndRemove($offer, $data['reason']);

                                    $livewire->dispatch('close-modal');
                                    $livewire->dispatch('refreshWaitingList');
                                })
                                ->successNotificationTitle('Offer rescinded and member removed from waiting list'),
                        ];
                    })
                    ->form(function (?WaitingListAccount $record) {
                        if (! $record) {
                            return [];
                        }

                        $offer = $record->trainingPlaceOffers()
                            ->where('status', TrainingPlaceOfferStatus::Pending->value)
                            ->latest()
                            ->first();

                        $offerFields = $offer ? [
                            Forms\Components\Fieldset::make('training_place_offer')
                                ->label('Active Training Place Offer')
                                ->schema([
                                    Forms\Components\Placeholder::make('offer_status')
                                        ->label('Status')
                                        ->content($offer->status->label()),

                                    Forms\Components\Placeholder::make('offer_position')
                                        ->label('Position')
                                        ->content($offer->trainingPosition->position->name),

                                    Forms\Components\Placeholder::make('offer_expires_at')
                                        ->label('Expires At')
                                        ->content($offer->expires_at->format('d/m/Y H:i').' UTC'),

                                    Forms\Components\Placeholder::make('offer_responded_at')
                                        ->label('Member Responded At')
                                        ->content($offer->response_at?->format('d/m/Y H:i') ?? '—'),
                                ])
                                ->columns(2),
                        ] : [];

                        return [
                            Forms\Components\Fieldset::make('base_information')
                                ->label('Base Information')
                                ->schema([
                                    CopyablePlaceholder::make('id')
                                        ->label('CID')
                                        ->content(fn (WaitingListAccount $record) => $record->account_id)
                                        ->iconOnly(),

                                    CopyablePlaceholder::make('name')
                                        ->label('Name')
                                        ->content(fn (WaitingListAccount $record) => $record->account->name)
                                        ->iconOnly(),

                                    Forms\Components\Placeholder::make('position')
                                        ->label('Position')
                                        ->content(function (WaitingListAccount $record) {
                                            return sprintf(
                                                '%s of %d',
                                                $this->ownerRecord->positionOf($record) ?? '-',
                                                $this->ownerRecord->waitingListAccounts->count()
                                            );
                                        }),
                                ]),

                            ...$offerFields,
                        ];
                    }),

                Tables\Actions\EditAction::make()
                    ->using(function (WaitingListAccount $record, $data, $livewire) {
                        $record->update([
                            'notes' => $data['notes'],
                        ]);

                        $flagsById = collect(Arr::get($data, 'flags', []));
                        // only update manual flags
                        $flagsToUpdate = $record->flags->filter(fn ($flag) => $flag->position_group_id == null);
                        $flagsToUpdate->each(fn ($flag) => $flagsById->get($flag->id) ? $flag->pivot->mark() : $flag->pivot->unMark());

                        $record->flags()->sync(
                            $flagsById->mapWithKeys(fn ($value, $key) => [$key => ['marked_at' => $value ? now() : null]])->all(),
                        );

                        $livewire->dispatch('refreshWaitingList');

                        return $record;
                    })
                    ->visible(fn ($record) => $this->can('updateAccounts', $record->waitingList)),

                Tables\Actions\DetachAction::make('detachWithReason')
                    ->label('Remove')
                    ->form([
                        Forms\Components\Select::make('reason_type')
                            ->label('Reason for removal')
                            ->options(self::removalReasonOptions())
                            ->required()
                            ->reactive(),

                        Forms\Components\Textarea::make('custom_reason')
                            ->label('Custom reason')
                            ->rows(3)
                            ->required()
                            ->visible(fn (callable $get) => $get('reason_type') === 'other'),
                    ])
                    ->action(function (WaitingListAccount $record, array $data, $livewire) {
                        $removalType = $data['reason_type'];

                        $removal = new WaitingList\Removal(WaitingList\RemovalReason::from($removalType), auth()->user()->id, $data['custom_reason'] ?? '');

                        $livewire->ownerRecord->removeFromWaitingList($record->account, $removal);
                        $livewire->dispatch('refreshWaitingList');
                    })
                    ->successNotificationTitle('User removed from waiting list')
                    ->modalHeading('Remove from Waiting List')
                    ->modalDescription('Please provide a reason for removing this user.')
                    ->modalSubmitActionLabel('Remove')
                    ->modalCancelActionLabel('Cancel')
                    ->visible(fn ($record) => $this->can('removeAccounts', $record->waitingList)),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('manualSetupTrainingPlace')
                        ->label('Manual Setup Training Place')
                        ->icon('heroicon-o-academic-cap')
                        ->visible(fn ($record) => $this->can('trainingPlacesManualSetup', $record->waitingList))
                        ->form([
                            Forms\Components\Select::make('training_position_id')
                                ->label('Training Position')
                                ->options(function ($livewire) {
                                    return $livewire->ownerRecord->trainingPositions
                                        ->mapWithKeys(fn ($tp) => [$tp->id => $tp->position?->callsign ?? "Position #{$tp->id}"])
                                        ->toArray();
                                })
                                ->required()
                                ->helperText('Select the training position to offer to this user.'),
                        ])
                        ->action(function (WaitingListAccount $record, array $data, $livewire) {
                            $trainingPosition = $livewire->ownerRecord->trainingPositions()->findOrFail($data['training_position_id']);

                            $service = app(TrainingPlaceService::class);
                            $trainingPlace = $service->createManualTrainingPlace($record, $trainingPosition);

                            Notification::make()
                                ->title('Training place offered successfully')
                                ->success()
                                ->actions([
                                    NotificationAction::make('view')
                                        ->label('View Training Place')
                                        ->url(ViewTrainingPlace::getUrl(['trainingPlaceId' => $trainingPlace->id]))
                                        ->markAsRead(),
                                ])
                                ->send();

                            $livewire->dispatch('refreshWaitingList');
                        })
                        ->successNotificationTitle('Training place offered successfully')
                        ->modalHeading('Manual Setup Training Place')
                        ->modalDescription('Select a training position to manually setup a training place for this user.')
                        ->modalSubmitActionLabel('Setup Training Place')
                        ->modalCancelActionLabel('Cancel'),
                ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->persistSearchInSession()
            ->paginated(['25', '50', '100'])
            ->defaultPaginationPageOption(25);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()->can('view', $ownerRecord);
    }

    protected function canView(Model $record): bool
    {
        return true;
    }

    protected function canEdit(Model $record): bool
    {
        return $this->can('updateAccounts', $this->getOwnerRecord());
    }

    protected function canAttach(): bool
    {
        return $this->can('addAccounts', $this->getOwnerRecord());
    }

    protected function canDetach(Model $record): bool
    {
        return $this->can('removeAccount', $this->getOwnerRecord());
    }

    public static function removalReasonOptions(): array
    {
        return WaitingList\RemovalReason::formOptions();
    }

    // Display All Manual Flags where display option is enabled
    protected function getFlagColumns(): array
    {
        return $this->ownerRecord->flags()
            ->where('display_in_table', true)
            ->get()
            ->map(function ($flag) {
                return Tables\Columns\IconColumn::make("flag_{$flag->id}")
                    ->label($flag->name)
                    ->boolean()
                    ->getStateUsing(function (WaitingListAccount $record) use ($flag) {
                        $flagRecord = $record->flags->firstWhere('id', $flag->id);

                        return $flagRecord?->pivot?->marked_at !== null;
                    });
            })->all();
    }
}
