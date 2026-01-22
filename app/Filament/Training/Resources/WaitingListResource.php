<?php

namespace App\Filament\Training\Resources;

use App\Filament\Training\Resources\WaitingListResource\RelationManagers\AccountsRelationManager;
use App\Models\Training\WaitingList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
                TextInput::make('name')->autofocus()->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),
                TextInput::make('slug')->required(),

                Select::make('department')->options([
                    'atc' => 'ATC Training',
                    'pilot' => 'Pilot Training',
                ])->disabledOn('edit')->required(),

                Section::make('Additional Settings')
                    ->schema([
                        Toggle::make('feature_toggles.check_atc_hours')
                            ->label('Check ATC Hours')
                            ->default(true),

                        Toggle::make('feature_toggles.display_on_roster')
                            ->label('Display Roster Membership')
                            ->default(true),

                        Toggle::make('feature_toggles.check_cts_theory_exam')
                            ->label('Display CTS Theory Exam')
                            ->default(true),

                        Toggle::make('requires_roster_membership')
                            ->label('Require Roster Membership')
                            ->default(true),

                        Toggle::make('self_enrolment_enabled')
                            ->label('Enable Self-Enrolment')
                            ->default(false)
                            ->live(),

                        TextInput::make('max_capacity')
                            ->label('Maximum Capacity')
                            ->helperText('Leave empty for unlimited capacity. Set a number to limit how many users can be on this waiting list.')
                            ->integer()
                            ->minValue(1),

                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Self-Enrolment Requirements')
                    ->schema([
                        Select::make('self_enrolment_minimum_qualification_id')
                            ->label('Minimum Rating')
                            ->helperText('If the member has a rating lower than this, they cannot self-enrol.')
                            ->relationship('minimumQualification', 'code')
                            ->searchable()
                            ->preload(),
                        Select::make('self_enrolment_maximum_qualification_id')
                            ->label('Maximum Rating')
                            ->helperText('If the member has a rating higher than this, they cannot self-enrol.')
                            ->relationship('maximumQualification', 'code')
                            ->searchable()
                            ->preload(),
                        Select::make('self_enrolment_hours_at_qualification_id')
                            ->label('Rating for Hour Check')
                            ->helperText('Check if the member has a certain number of hours at this rating.')
                            ->relationship('hoursAtQualification', 'code')
                            ->searchable()
                            ->preload(),
                        TextInput::make('self_enrolment_hours_at_qualification_minimum_hours')
                            ->label('Minimum Hours')
                            ->helperText('Minimum hours required at the above rating to self-enrol.')
                            ->integer()
                            ->minValue(0),
                    ])->collapsible()->collapsed()->visible(fn (callable $get) => $get('self_enrolment_enabled') === true),
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
            'index' => \App\Filament\Training\Resources\WaitingListResource\Pages\ListWaitingLists::route('/'),
            'create' => \App\Filament\Training\Resources\WaitingListResource\Pages\CreateWaitingList::route('/create'),
            'edit' => \App\Filament\Training\Resources\WaitingListResource\Pages\EditWaitingList::route('/{record}/edit'),
            'view' => \App\Filament\Training\Resources\WaitingListResource\Pages\ViewWaitingList::route('/{record}'),
        ];
    }
}
