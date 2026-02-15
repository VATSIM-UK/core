<?php

namespace App\Filament\Admin\Resources\VisitTransfer;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource\Pages;
use App\Models\VisitTransfer\Application;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VisitTransferApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-end-on-rectangle';

    protected static ?string $navigationGroup = 'Visiting / Transferring';

    protected static ?string $label = 'Applications';

    public static function canAccess(): bool
    {

        return auth()->user()->can('vt.application.view.*');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                TextColumn::make('public_id')->label('ID'),
                TextColumn::make('account_id')->label('Account ID')->searchable(),
                TextColumn::make('account.name')->label('Full Name')->searchable(['name_first', 'name_last']),
                TextColumn::make('facility.name')->label('Facility Name'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->status_string)
                    ->color(fn ($record) => $record->status_color),
                TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        Application::STATUS_IN_PROGRESS => 'In Progress',
                        Application::STATUS_WITHDRAWN => 'Withdrawn',
                        Application::STATUS_EXPIRED => 'Expired Automatically',
                        Application::STATUS_SUBMITTED => 'Submitted',
                        Application::STATUS_UNDER_REVIEW => 'Under Review',
                        Application::STATUS_ACCEPTED => 'Accepted',
                        Application::STATUS_COMPLETED => 'Completed',
                        Application::STATUS_LAPSED => 'Lapsed',
                        Application::STATUS_CANCELLED => 'Cancelled',
                        Application::STATUS_REJECTED => 'Rejected',
                    ])->searchable()->preload()->multiple(),
                SelectFilter::make('facility_id')
                    ->label('Facility')
                    ->relationship('facility', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])->paginationPageOptions([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVisitTransferApplications::route('/'),
            'view' => Pages\ViewVisitTransferApplication::route('/{record}'),
        ];
    }
}
