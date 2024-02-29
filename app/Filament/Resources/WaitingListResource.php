<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WaitingListResource\Pages;
use App\Filament\Resources\WaitingListResource\RelationManagers\AccountsRelationManager;
use App\Models\Training\WaitingList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class WaitingListResource extends Resource
{
    protected static ?string $model = WaitingList::class;

    protected static ?string $navigationGroup = 'Training';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->autofocus()->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                Forms\Components\TextInput::make('slug')->required(),

                Forms\Components\Select::make('department')->options([
                    'atc' => 'ATC Training',
                    'pilot' => 'Pilot Training',
                ])->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('accounts', [
                AccountsRelationManager::class,
            ]),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWaitingLists::route('/'),
            'create' => Pages\CreateWaitingList::route('/create'),
            'view' => Pages\ViewWaitingList::route('/{record}'),
        ];
    }
}
