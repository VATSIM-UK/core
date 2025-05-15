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

                Forms\Components\Fieldset::make("Additional Settings")
                ->schema([
                    Forms\Components\Toggle::make('feature_toggles.check_atc_hours')
                    ->label('Check ATC Hours')
                    ->default(true),

                    Forms\Components\Toggle::make('feature_toggles.check_cts_theory_exam')
                    ->label('Enable CTS Theory Exam')
                    ->default(true),

                    Forms\Components\Toggle::make('requires_roster_membership')
                    ->label('Requires Roster Membership')
                    ->default(true),
                    
                    Forms\Components\Toggle::make('self_enrolment_enabled')
                    ->label('Enable Self-Enrolment')
                    ->default(false)
                    ->live(),

                ]),
                
                Forms\Components\Fieldset::make("Self-Enrolment Requirements")
                ->schema([
                    Forms\Components\Select::make('self_enrolment_minimum_qualification_id')
                    ->label('Minimum Qualification ID')
                    ->relationship('minimumQualification', 'name_long')
                    ->searchable()
                    ->preload(),
                    Forms\Components\Select::make('self_enrolment_maximum_qualification_id')
                    ->label('Maximum Qualification ID')
                    ->relationship('maximumQualification', 'name_long')
                    ->searchable()
                    ->preload(),
                    Forms\Components\Select::make('self_enrolment_hours_at_qualification_id')
                    ->label('Qualification ID (for Hours Requirement)')
                    ->relationship('hoursAtQualification', 'name_long')
                    ->searchable()
                    ->preload(),
                    Forms\Components\TextInput::make('self_enrolment_hours_at_qualification_minimum_hours')
                    ->label('Minimum hours at Qualification')
                    ->integer()
                    ->minValue(0),
                ])->visible(fn (callable $get) => $get('self_enrolment_enabled') === true),
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
