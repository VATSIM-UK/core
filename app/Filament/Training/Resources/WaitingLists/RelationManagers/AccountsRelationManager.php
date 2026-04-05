<?php

namespace App\Filament\Training\Resources\WaitingLists\RelationManagers;

use App\Enums\TrainingPlaceOfferStatus;
use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Services\Training\TrainingPlaceOfferService;
use App\Services\Training\TrainingPlaceService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
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

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('base_information')
                    ->label('Base Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('CID')
                            ->state(fn (WaitingListAccount $record) => (string) $record->account_id)
                            ->copyable(),

                        TextEntry::make('name')
                            ->label('Name')
                            ->state(fn (WaitingListAccount $record) => $record->account->name)
                            ->copyable(),

                        Placeholder::make('position')
                            ->label('Position')
                            ->content(function (WaitingListAccount $record) {
                                return sprintf(
                                    '%s of %d',
                                    $this->ownerRecord->positionOf($record) ?? '-',
                                    $this->ownerRecord->waitingListAccounts->count()
                                );
                            }),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Add notes here'),

                    ]),

                Fieldset::make('cts_theory_exam')
                    ->label('CTS Theory Exam')
                    ->schema(function (WaitingListAccount $record) {
                        return [
                            Toggle::make('cts_theory_exam')
                                ->label('Passed')
                                ->afterStateHydrated(fn ($component, $state) => $component->state((bool) $record->theory_exam_passed))
                                ->disabled(),
                        ];
                    })
                    ->visible(fn ($record) => $record->waitingList->feature_toggles['check_cts_theory_exam'] ?? true),

                Fieldset::make('manual_flags')
                    ->label('Manual Flags')
                    ->schema(function (WaitingListAccount $record) {
                        return $record->flags->filter(fn ($flag) => $flag->position_group_id == null)->map(function ($flag) {
                            return Toggle::make('flags.'.$flag->id)
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
                TextColumn::make('position')->getStateUsing(fn (WaitingListAccount $record) => $this->ownerRecord->positionOf($record) ?? '-')->label('Position'),
                TextColumn::make('account_id')->label('CID')->searchable(),
                TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
                IconColumn::make('on_roster')->boolean()->label('On Roster')->getStateUsing(fn (WaitingListAccount $record) => $record->account->onRoster())->visible(fn () => $this->ownerRecord->feature_toggles['display_on_roster'] ?? true),
                TextColumn::make('created_at')->label('Added On')->dateTime('d/m/Y H:i:s'),
                IconColumn::make('cts_theory_exam')->boolean()->label('CTS Theory Exam')->getStateUsing(fn (WaitingListAccount $record) => $record->theory_exam_passed)->visible(fn () => $this->ownerRecord->feature_toggles['check_cts_theory_exam'] ?? true),
                ...$this->getFlagColumns(),
            ])
            ->recordActions([
                Action::make('offerTrainingPlace')
                    ->label('Offer Training Place')
                    ->icon('heroicon-o-academic-cap')
                    ->visible(function (WaitingListAccount $record) {
                        return $this->can('offerTrainingPlace', $record->waitingList) && ! $record->hasPendingTrainingPlaceOffer();
                    })
                    ->schema(function (WaitingListAccount $record) {
                        $recentFeedback = Feedback::ATC()
                            ->where('account_id', $record->account_id)
                            ->with(['answers.question'])
                            ->where('created_at', '>=', now()->subMonths(3))
                            ->latest()
                            ->get();

                        $feedbackEntries = $recentFeedback->map(fn (Feedback $feedback) => Section::make("Feedback - {$feedback->created_at->format('d/m/Y H:i')}")
                            ->schema([
                                ...$feedback->answers->map(fn ($answer) => Placeholder::make("answer_{$answer->id}")
                                    ->label($answer->question?->question ?? 'Unknown Question')
                                    ->content($answer->response ?? 'Question not answered')
                                )->all(),
                            ])
                            ->columns(3)
                            ->collapsible()
                        )->all();

                        return [
                            Section::make('Member Feedback')
                                ->description('Displaying ATC feedback entries from the last 3 months only.')
                                ->schema($feedbackEntries ?: [
                                    Placeholder::make('no_feedback')
                                        ->label('')
                                        ->content('No feedback on record for this member.'),
                                ])
                                ->collapsible()
                                ->columns(3),

                            Select::make('training_position_id')
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

                ViewAction::make()
                    ->modalHeading(fn (?WaitingListAccount $record) => "{$record?->account->name} - Waiting List Account")
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
                            Action::make('rescind')
                                ->label('Rescind Offer')
                                ->color('danger')
                                ->icon('heroicon-o-x-circle')
                                ->visible(fn ($record) => $this->can('rescindTrainingPlaceOffer', $record->waitingList))
                                ->modalHeading('Rescind Training Place Offer')
                                ->modalDescription('The member will be notified. Their waiting list position will be retained.')
                                ->modalSubmitActionLabel('Rescind Offer')
                                ->schema([
                                    Textarea::make('reason')
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

                            Action::make('rescindAndRemove')
                                ->label('Rescind Offer & Remove')
                                ->color('danger')
                                ->icon('heroicon-o-trash')
                                ->visible(fn ($record) => $this->can('rescindTrainingPlaceOffer', $record->waitingList) && $this->can('removeAccount', $record->waitingList))
                                ->modalHeading('Rescind Offer & Remove from Waiting List')
                                ->modalDescription('The member will be removed from the waiting list entirely. This cannot be undone.')
                                ->modalSubmitActionLabel('Rescind & Remove')
                                ->schema([
                                    Textarea::make('reason')
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
                    ->schema(function (?WaitingListAccount $record) {
                        if (! $record) {
                            return [];
                        }

                        $offer = $record->trainingPlaceOffers()
                            ->where('status', TrainingPlaceOfferStatus::Pending->value)
                            ->latest()
                            ->first();

                        $offerFields = $offer ? [
                            Fieldset::make('training_place_offer')
                                ->label('Active Training Place Offer')
                                ->schema([
                                    Placeholder::make('offer_status')
                                        ->label('Status')
                                        ->content($offer->status->label()),

                                    Placeholder::make('offer_position')
                                        ->label('Position')
                                        ->content($offer->trainingPosition->position->name),

                                    Placeholder::make('offer_expires_at')
                                        ->label('Expires At')
                                        ->content($offer->expires_at->format('d/m/Y H:i').' UTC'),

                                    Placeholder::make('offer_responded_at')
                                        ->label('Member Responded At')
                                        ->content($offer->response_at?->format('d/m/Y H:i') ?? '—'),
                                ])
                                ->columns(2),
                        ] : [];

                        return [
                            Fieldset::make('base_information')
                                ->label('Base Information')
                                ->schema([
                                    TextEntry::make('id')
                                        ->label('CID')
                                        ->state(fn (WaitingListAccount $record) => (string) $record->account_id)
                                        ->copyable(),

                                    TextEntry::make('name')
                                        ->label('Name')
                                        ->state(fn (WaitingListAccount $record) => $record->account->name)
                                        ->copyable(),

                                    Placeholder::make('position')
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

                EditAction::make()
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

                DetachAction::make('detachWithReason')
                    ->label('Remove')
                    ->form([
                        Select::make('reason_type')
                            ->label('Reason for removal')
                            ->options(self::removalReasonOptions())
                            ->required()
                            ->reactive(),

                        Textarea::make('custom_reason')
                            ->label('Custom reason')
                            ->rows(3)
                            ->required()
                            ->visible(fn (callable $get) => $get('reason_type') === 'other'),
                    ])
                    ->action(function (WaitingListAccount $record, array $data, $livewire) {
                        $removalType = $data['reason_type'];

                        $removal = new Removal(RemovalReason::from($removalType), auth()->user()->id, $data['custom_reason'] ?? '');

                        $livewire->ownerRecord->removeFromWaitingList($record->account, $removal);
                        $livewire->dispatch('refreshWaitingList');
                    })
                    ->successNotificationTitle('User removed from waiting list')
                    ->modalHeading('Remove from Waiting List')
                    ->modalDescription('Please provide a reason for removing this user.')
                    ->modalSubmitActionLabel('Remove')
                    ->modalCancelActionLabel('Cancel')
                    ->visible(fn ($record) => $this->can('removeAccounts', $record->waitingList)),
                ActionGroup::make([
                    Action::make('manualSetupTrainingPlace')
                        ->label('Manual Setup Training Place')
                        ->icon('heroicon-o-academic-cap')
                        ->visible(fn ($record) => $this->can('trainingPlacesManualSetup', $record->waitingList))
                        ->schema([
                            Select::make('training_position_id')
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
                                    Action::make('view')
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
        return RemovalReason::formOptions();
    }

    // Display All Manual Flags where display option is enabled
    protected function getFlagColumns(): array
    {
        return $this->ownerRecord->flags()
            ->where('display_in_table', true)
            ->get()
            ->map(function ($flag) {
                return IconColumn::make("flag_{$flag->id}")
                    ->label($flag->name)
                    ->boolean()
                    ->getStateUsing(function (WaitingListAccount $record) use ($flag) {
                        $flagRecord = $record->flags->firstWhere('id', $flag->id);

                        return $flagRecord?->pivot?->marked_at !== null;
                    });
            })->all();
    }
}
