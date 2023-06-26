<?php

namespace App\Filament\Resources;

use App\Enums\QualificationTypeEnum;
use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers\QualificationsRelationManager;
use App\Filament\Resources\AccountResource\RelationManagers\RolesRelationManager;
use App\Filament\Resources\AccountResource\RelationManagers\StatesRelationManager;
use App\Models\Mship\Account;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Contracts\Database\Eloquent\Builder;
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
                Fieldset::make('Basic Details')->schema([
                    Grid::make(3)->schema([
                        Placeholder::make('Central Account Name')->content(fn ($record) => $record->name_first.' '.$record->name_last)->visibleOn('view'),
                        TextInput::make('nickname')->label('Preferred Name'),
                        Placeholder::make('id')->label('CID')->content(fn ($record) => $record->id)->visibleOn('view'),

                    ]),

                    Fieldset::make('Emails')->schema([
                        TextInput::make('email')->label('Primary Email')->required()->disabled()->visibleOn('view'),

                        Repeater::make('secondaryEmails')->relationship()->schema([TextInput::make('email')])->visibleOn('view'),
                    ])->visibleOn('view')->when(fn ($record) => auth()->user()->can("account.view-sensitive.$record->id")),

                    Fieldset::make('State')->schema([
                        Grid::make(3)->schema([
                            Placeholder::make('vatsim_region')->label('VATSIM Region')->content(fn ($record) => $record->primary_permanent_state->pivot->region),
                            Placeholder::make('vatsim_division')->label('VATSIM Division')->content(fn ($record) => $record->primary_permanent_state->pivot->division),
                            Placeholder::make('uk_primary_state')->label('UK Primary State')->content(fn ($record) => $record->primary_state->name),
                        ]),
                    ])->visibleOn('view'),

                    Fieldset::make('Qualifications')->schema(function ($record) {
                        return [
                            Grid::make(3)->schema(static::makeQualificationSummaryPlaceholders($record))->visibleOn('view'),
                        ];
                    }),
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
                    ->tooltip(fn ($record) => $record->is_banned ? ($record->is_network_banned ? 'Banned on VATSIM.NET' : ('Banned locally for another '.$record->system_ban->period_amount_string)) : null),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('banned')
                    ->queries(
                        true: fn (Builder $query) => $query->banned(),
                        false: fn (Builder $query) => $query->notBanned(),
                    ),
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
            StatesRelationManager::class,
            QualificationsRelationManager::class,
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

    private static function makeQualificationSummaryPlaceholders($record): array
    {
        return $record->active_qualifications->map(function ($qualification) {
            return Placeholder::make("qualification_{$qualification->type}")->label(QualificationTypeEnum::from($qualification->type)->human())->content("{$qualification->name_long} ({$qualification->code})");
        })->all();
    }
}
