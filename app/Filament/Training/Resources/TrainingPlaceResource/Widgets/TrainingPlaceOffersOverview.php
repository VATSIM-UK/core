<?php

namespace App\Filament\Training\Resources\TrainingPlaceResource\Widgets;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use App\Models\Training\WaitingList;
use App\Services\Training\TrainingPlaceOfferService;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TrainingPlaceOffersOverview extends BaseWidget
{
    protected static ?string $heading = 'Training Place Offers';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TrainingPlaceOffer::query()
                    ->with([
                        'waitingListAccount' => fn ($query) => $query->withTrashed(),
                        'waitingListAccount.account',
                        'waitingListAccount.waitingList',
                        'trainingPosition.position',
                    ])
                    ->whereHas('waitingListAccount', function (Builder $query): void {
                        $authorisedWaitingListIds = WaitingList::all()
                            ->filter(fn (WaitingList $waitingList) => auth()->user()->can('viewTrainingPlaceOffer', $waitingList))
                            ->pluck('id');

                        $query->withTrashed()->whereIn('list_id', $authorisedWaitingListIds);
                    })
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('waitingListAccount.account.name')
                    ->label('Student')
                    ->searchable(['name_first', 'name_last']),

                Tables\Columns\TextColumn::make('waitingListAccount.account_id')
                    ->label('CID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('trainingPosition.position.callsign')
                    ->label('Position'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TrainingPlaceOfferStatus $state): string => match ($state) {
                        TrainingPlaceOfferStatus::Pending => 'warning',
                        TrainingPlaceOfferStatus::Accepted => 'success',
                        TrainingPlaceOfferStatus::Declined => 'danger',
                        TrainingPlaceOfferStatus::Rescinded => 'danger',
                        TrainingPlaceOfferStatus::Expired => 'danger',
                    })
                    ->formatStateUsing(fn (TrainingPlaceOfferStatus $state): string => $state->label())
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Offered At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('response_at')
                    ->label('Responded At')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->actions([
                Tables\Actions\Action::make('rescind')
                    ->label('Rescind Offer')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(function (TrainingPlaceOffer $record): bool {
                        if ($record->status !== TrainingPlaceOfferStatus::Pending) {
                            return false;
                        }

                        $waitingList = $record->waitingListAccount?->waitingList;

                        return $waitingList && auth()->user()->can('rescindTrainingPlaceOffer', $waitingList);
                    })
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
                    ->action(function (TrainingPlaceOffer $record, array $data): void {
                        app(TrainingPlaceOfferService::class)->rescindOffer($record, $data['reason']);
                    })
                    ->successNotificationTitle('Offer rescinded'),

                Tables\Actions\Action::make('rescindAndRemove')
                    ->label('Rescind & Remove')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->visible(function (TrainingPlaceOffer $record): bool {
                        if ($record->status !== TrainingPlaceOfferStatus::Pending) {
                            return false;
                        }

                        $waitingList = $record->waitingListAccount?->waitingList;

                        return $waitingList
                            && auth()->user()->can('rescindTrainingPlaceOffer', $waitingList)
                            && auth()->user()->can('removeAccount', $waitingList);
                    })
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
                    ->action(function (TrainingPlaceOffer $record, array $data): void {
                        app(TrainingPlaceOfferService::class)->rescindOfferAndRemove($record, $data['reason']);
                    })
                    ->successNotificationTitle('Offer rescinded and member removed from waiting list'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->default(TrainingPlaceOfferStatus::Pending->value)
                    ->options(TrainingPlaceOfferStatus::class),

                Filter::make('offered_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('Offered from')
                            ->default(now()->subMonths(3)),
                        DatePicker::make('until')
                            ->label('Offered until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn (Builder $q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn (Builder $q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Offered from '.date('d/m/Y', strtotime($data['from'])))
                                ->removeField('from');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Offered until '.date('d/m/Y', strtotime($data['until'])))
                                ->removeField('until');
                        }

                        return $indicators;
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No training place offers')
            ->emptyStateDescription('No offers match the current filter.');
    }
}
