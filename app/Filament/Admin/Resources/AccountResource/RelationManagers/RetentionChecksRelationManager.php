<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RetentionChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'retentionChecks';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Retention Checks';

    protected function getLogActionName(): string
    {
        return 'ViewRetentionChecks';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                return $query->with(['waitingListAccount.waitingList']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('waitingListAccount.waitingList.name')
                    ->label('Waiting List')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        WaitingListRetentionCheck::STATUS_PENDING => 'warning',
                        WaitingListRetentionCheck::STATUS_USED => 'success',
                        WaitingListRetentionCheck::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('token')
                    ->label('Token')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
                Tables\Columns\TextColumn::make('email_sent_at')->dateTime()->label('Email Sent')->sortable(),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->label('Expires')->sortable(),
                Tables\Columns\TextColumn::make('response_at')->dateTime()->label('Responded')->sortable(),
                Tables\Columns\TextColumn::make('removal_actioned_at')->dateTime()->label('Removal Actioned')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Created')->toggleable(isToggledHiddenByDefault: true)->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->label('Updated')->toggleable(isToggledHiddenByDefault: true)->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'used' => 'Used',
                        'expired' => 'Expired',
                    ])
                    ->label('Status'),
            ])
            ->defaultSort('created_at', 'desc')
            ->bulkActions([])
            ->headerActions([]);
    }
}
