<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class RetentionChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'retentionChecks';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Retention Checks';

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
                TextColumn::make('waitingListAccount.waitingList.name')
                    ->label('Waiting List')
                    ->sortable(),
                TextColumn::make('status_human')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state, WaitingListRetentionCheck $record) => match ($record->status) {
                        WaitingListRetentionCheck::STATUS_PENDING => 'warning',
                        WaitingListRetentionCheck::STATUS_USED => 'success',
                        WaitingListRetentionCheck::STATUS_EXPIRED => 'danger',
                        default => 'gray',
                    })
                    ->sortable('status'),
                TextColumn::make('email_sent_at')->dateTime()->label('Email Sent')->sortable(),
                TextColumn::make('expires_at')->dateTime()->label('Expires')->sortable(),
                TextColumn::make('response_at')->dateTime()->label('Responded')->sortable(),
                TextColumn::make('removal_actioned_at')->dateTime()->label('Removal Actioned')->sortable(),
                TextColumn::make('created_at')->dateTime()->label('Created')->toggleable(isToggledHiddenByDefault: true)->sortable(),
                TextColumn::make('token')->label('Token')->toggleable(isToggledHiddenByDefault: true)->copyable(),
            ])
            ->groups([
                Group::make('waitingListAccount.waitingList.name')
                    ->label('Waiting List')
                    ->collapsible(),
            ])
            ->groupingSettingsHidden()
            ->defaultGroup('waitingListAccount.waitingList.name')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'used' => 'Used',
                        'expired' => 'Expired',
                    ])
                    ->label('Status'),
            ])
            ->toolbarActions([])
            ->headerActions([]);
    }
}
