<?php

namespace App\Filament\Resources;

use App\Enums\QualificationTypeEnum;
use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Mship\Account;
use Carbon\CarbonInterface;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

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
                Forms\Components\Fieldset::make('Basic Details')->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Placeholder::make('Central Account Name')
                            ->content(fn ($record) => $record->name_first.' '.$record->name_last)
                            ->visibleOn('view'),
                        Forms\Components\TextInput::make('nickname')
                            ->label('Preferred Name'),
                        Forms\Components\Placeholder::make('id')
                            ->label('CID')
                            ->content(fn ($record) => $record->id)
                            ->visibleOn('view'),

                    ]),

                    Forms\Components\Fieldset::make('Emails')->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('Primary Email')
                            ->required()
                            ->disabled(),

                        Forms\Components\Repeater::make('secondaryEmails')
                            ->relationship()
                            ->schema([Forms\Components\TextInput::make('email')])->disabled(),
                    ])->when(fn ($record, $context) => auth()->user()->can('viewSensitive', $record) && $context === 'view'),

                    Forms\Components\Fieldset::make('State')->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Placeholder::make('vatsim_region')
                                ->label('VATSIM Region')
                                ->content(fn ($record) => $record->primary_permanent_state?->pivot?->region),
                            Forms\Components\Placeholder::make('vatsim_division')
                                ->label('VATSIM Division')
                                ->content(fn ($record) => $record->primary_permanent_state?->pivot?->division),
                            Forms\Components\Placeholder::make('uk_primary_state')
                                ->label('UK Primary State')
                                ->content(fn ($record) => $record->primary_state?->name),
                        ]),
                    ])->visibleOn('view'),

                    Forms\Components\Fieldset::make('Qualifications')->schema(function ($record) {
                        return [
                            Forms\Components\Grid::make(3)
                                ->schema(static::makeQualificationSummaryPlaceholders($record))
                                ->visibleOn('view'),
                        ];
                    })->visibleOn('view'),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable()->searchable()->label('ID'),
                Tables\Columns\TextColumn::make('name')->sortable()->searchable(['name_first', 'name_last']),
                Tables\Columns\TextColumn::make('qualification_atc')->sortable()->label('ATC Rating'),
                Tables\Columns\TextColumn::make('qualification_pilot')->sortable()->label('Pilot Rating'),
                Tables\Columns\BadgeColumn::make('state')
                    ->getStateUsing(fn ($record) => $record->primary_state?->name)
                    ->colors([
                        'primary' => static fn ($state) => in_array($state, ['Division', 'Transferring']),
                        'secondary' => static fn ($state) => in_array($state, ['Visiting']),
                    ]),
                Tables\Columns\IconColumn::make('is_banned')->label('Banned')
                    ->boolean()
                    ->falseIcon('')
                    ->trueColor('danger')
                    ->tooltip(fn ($record) => $record->is_banned ? ($record->is_network_banned ? 'Banned on VATSIM.NET' : ('Banned locally for another '.now()->diffForHumans($record->system_ban->period_finish, CarbonInterface::DIFF_ABSOLUTE))) : null),
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
            RelationManagers\StatesRelationManager::class,
            RelationManagers\QualificationsRelationManager::class,
            RelationManagers\RolesRelationManager::class,
            RelationManagers\BansRelationManager::class,
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

    /** Maps the account's active qualifications into a set of placeholder fields */
    private static function makeQualificationSummaryPlaceholders($record): array
    {
        return $record->active_qualifications->map(function ($qualification) {
            return Forms\Components\Placeholder::make("qualification_{$qualification->type}")
                ->label(QualificationTypeEnum::from($qualification->type)->human())
                ->content("{$qualification->name_long} ({$qualification->code})");
        })->all();
    }
}
