<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers\RolesRelationManager;
use App\Models\Mship\Account;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationGroup = 'User Management';

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'name_first', 'name_last', 'nickname'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'VATSIM ID' => $record->id,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('Basic Details')->schema([
                    TextInput::make('name_first')->label('First Name')->required()->disabled()->visibleOn('view'),
                    TextInput::make('name_last')->label('Last Name')->required()->disabled()->visibleOn('view'),
                    TextInput::make('nickname')->label('Preferred First Name')->visibleOn('edit'),
                    TextInput::make('id')->required()->autofocus()->disabled()->label('CID')->visibleOn('view'),

                    TextInput::make('email')->label('Primary Email')->required()->disabled()->visibleOn('view'),

                    Repeater::make('secondaryEmails')->relationship()->schema([TextInput::make('email')])->visibleOn('view'),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(['name_first', 'name_last']),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RolesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccounts::route('/'),
            'view' => Pages\ViewAccount::route('/{record}'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
