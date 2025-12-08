<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Models\VisitTransfer\Application;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VisitTransferRelationManager extends RelationManager
{
    protected static string $relationship = 'visitTransferApplications';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return 'VT Applications';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Type')->formatStateUsing(fn ($state) => $state === Application::TYPE_VISIT ? 'Visit' : 'Transfer'),
                Tables\Columns\TextColumn::make('facility.name')->label('Facility'),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->formatStateUsing(fn ($state, $record) => $record->status_string)->colors([
                    'success' => fn ($state) => in_array($state, [Application::STATUS_ACCEPTED, Application::STATUS_COMPLETED]),
                    'warning' => fn ($state) => in_array($state, [Application::STATUS_UNDER_REVIEW, Application::STATUS_IN_PROGRESS, Application::STATUS_SUBMITTED]),
                    'danger' => fn ($state) => in_array($state, [Application::STATUS_REJECTED, Application::STATUS_CANCELLED, Application::STATUS_WITHDRAWN, Application::STATUS_EXPIRED, Application::STATUS_LAPSED]),
                ]),
                Tables\Columns\TextColumn::make('created_at')->label('Submitted At')->dateTime()->isoDateTimeFormat('lll'),
                Tables\Columns\TextColumn::make('updated_at')->label('Last Updated')->dateTime()->isoDateTimeFormat('lll'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make('View')
                    ->label('View Application')
                    ->url(fn ($record) => route('adm.visiting.application.view', ['application' => $record->id]))
                    ->color('primary')
                    ->visible(fn () => auth()->user()->can('adm/visit-transfer')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
