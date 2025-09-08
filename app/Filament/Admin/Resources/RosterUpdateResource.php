<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RosterUpdates\Pages\ListRosterUpdates;
use App\Filament\Admin\Resources\RosterUpdates\Pages\ViewRosterUpdate;
use App\Models\RosterUpdate;
use App\Models\Training\WaitingList;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RosterUpdateResource extends Resource
{
    protected static ?string $model = RosterUpdate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-list-bullet';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Details')->schema([
                    DatePicker::make('created_at')->label('Ran'),
                    DatePicker::make('period_start'),
                    DatePicker::make('period_end'),
                ]),
                Section::make('Data')
                    ->statePath('data')
                    ->schema([
                        TextInput::make('meetHourRequirement')->label('Controllers meeting requirement'),
                        TextInput::make('ganderControllers')->label('Eligible Gander/Oceanic controllers'),
                        TextInput::make('removeFromRoster')->label('Removed from roster'),
                        TextInput::make('homeRemovals')->label('Home members removed from roster'),
                        TextInput::make('visitingAndTransferringRemovals')->label('V/T removed from roster'),
                        TextInput::make('removeFromWaitingList')->label('Removed from waiting lists')
                            ->formatStateUsing(fn ($state) => collect($state)->map(function ($value, $key) {
                                $waitingList = WaitingList::withTrashed()->where('id', '=', $key)?->first();

                                return "$waitingList?->name: $value";
                            })->implode('; ')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Ran'),
                TextColumn::make('period_start'),
                TextColumn::make('period_end'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])->defaultSort('created_at', 'DESC');
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
            'index' => ListRosterUpdates::route('/'),
            'view' => ViewRosterUpdate::route('/{record}'),
        ];
    }
}
