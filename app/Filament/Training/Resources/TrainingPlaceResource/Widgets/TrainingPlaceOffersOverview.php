<?php

namespace App\Filament\Training\Resources\TrainingPlaceResource\Widgets;

use App\Enums\TrainingPlaceOfferStatus;
use App\Models\Training\TrainingPlace\TrainingPlaceOffer;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TrainingPlaceOffersOverview extends BaseWidget
{
    protected static ?string $heading = 'Training Place Offers';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(TrainingPlaceOffer::query()->with(['waitingListAccount.account', 'trainingPosition.position'])->withTrashed()->latest())
            ->columns([
                Tables\Columns\TextColumn::make('waitingListAccount.account_id')
                    ->label('CID')
                    ->searchable(),

                Tables\Columns\TextColumn::make('waitingListAccount.account.name')
                    ->label('Student')
                    ->searchable(['name_first', 'name_last']),

                Tables\Columns\TextColumn::make('trainingPosition.position.callsign')
                    ->label('Position')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (TrainingPlaceOfferStatus $state): string => match ($state) {
                        TrainingPlaceOfferStatus::Pending   => 'warning',
                        TrainingPlaceOfferStatus::Accepted  => 'success',
                        TrainingPlaceOfferStatus::Declined  => 'danger',
                        TrainingPlaceOfferStatus::Rescinded => 'danger',
                        TrainingPlaceOfferStatus::Expired   => 'danger',
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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(TrainingPlaceOfferStatus::class),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->emptyStateHeading('No training place offers')
            ->emptyStateDescription('No offers match the current filter.');
    }
}