<?php

namespace App\Filament\Training\Resources\Seminars\RelationManagers;

use App\Enums\SeminarInvitationStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvitationsRelationManager extends RelationManager
{
    protected static string $relationship = 'invitations';

    protected static ?string $title = 'Invitations';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('account'))
            ->columns([
                TextColumn::make('account_id')->label('CID')->searchable(),
                TextColumn::make('account.name')->label('Name')->searchable(['name_first', 'name_last']),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state?->label())
                    ->color(fn ($state) => $state?->color()),
                TextColumn::make('sent_at')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('responded_at')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(SeminarInvitationStatus::cases())->mapWithKeys(fn ($status) => [
                        $status->value => $status->label(),
                    ]))
                    ->multiple(),

                Filter::make('responded')
                    ->query(fn (Builder $query) => $query->whereIn('status', [
                        SeminarInvitationStatus::Attending->value,
                        SeminarInvitationStatus::NotInterested->value,
                        SeminarInvitationStatus::CannotAttend->value,
                    ])),
            ]);
    }
}
