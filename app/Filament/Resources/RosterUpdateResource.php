<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RosterUpdates\Pages\ListRosterUpdates;
use App\Filament\Resources\RosterUpdates\Pages\ViewRosterUpdate;
use App\Models\RosterUpdate;
use App\Models\Training\WaitingList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RosterUpdateResource extends Resource
{
    protected static ?string $model = RosterUpdate::class;

    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ->actions([
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
