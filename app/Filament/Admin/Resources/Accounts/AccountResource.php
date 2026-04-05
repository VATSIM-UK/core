<?php

namespace App\Filament\Admin\Resources\Accounts;

use App\Enums\QualificationTypeEnum;
use App\Filament\Admin\Helpers\Resources\DefinesGatedAttributes;
use App\Filament\Admin\Resources\Accounts\Pages\EditAccount;
use App\Filament\Admin\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Admin\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Admin\Resources\Accounts\RelationManagers\BansRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\EndorsementsRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\FeedbackRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\NotesRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\QualificationsRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\RetentionChecksRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\RolesRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\StatesRelationManager;
use App\Filament\Admin\Resources\Accounts\RelationManagers\VisitTransferRelationManager;
use App\Filament\Training\Resources\AccountResource\RelationManagers\WaitingListsRelationManager;
use App\Models\Mship\Account;
use App\Models\Roster;
use Carbon\CarbonInterface;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class AccountResource extends Resource implements DefinesGatedAttributes
{
    protected static ?string $model = Account::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'name_first', 'name_last', 'nickname', 'discord_id'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'VATSIM ID' => $record->id,
        ];
    }

    public static function gatedAttributes(Model $record): array
    {
        return [
            'email' => auth()->user()->can('viewSensitive', $record),
            'secondaryEmails' => auth()->user()->can('viewSensitive', $record),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return AccountResource::getUrl('view', ['record' => $record]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make('Basic Details')->columnSpanFull()->schema([
                    Grid::make(3)->columnSpanFull()->schema([
                        Placeholder::make('central_account_name')
                            ->content(fn ($record) => $record->name_first.' '.$record->name_last)
                            ->visibleOn('view'),
                        TextInput::make('nickname')
                            ->label('Preferred Name'),
                        TextEntry::make('id')
                            ->label('CID')
                            ->state(fn (Account $record) => (string) $record->id)
                            ->visibleOn('view')
                            ->copyable(),
                    ]),

                    Placeholder::make('has_secondary_password')->content(fn ($record) => $record->hasPassword() ? 'Yes' : 'No'),
                    Placeholder::make('discord_id')->label('Discord ID')->content(fn ($record) => $record->discord_id ?? new HtmlString('<i>Not Linked</i>')),
                    Placeholder::make('roster_status')->label('Roster Status')->content(fn ($record) => Roster::where('account_id', $record->id)->exists() ? 'Active' : 'Inactive'),
                    Placeholder::make('last_seen_controlling_uk')->label('Last UK Controlling Session')->content(fn ($record) => $record->lastSeenControllingUK()?->format('d M Y, H:i') ?? 'Never Controlled'),

                    Fieldset::make('Emails')->columnSpanFull()->schema([
                        TextInput::make('email')
                            ->label('Primary Email')
                            ->disabled()
                            ->copyable(),

                        Repeater::make('secondaryEmails')
                            ->relationship()
                            ->schema([TextInput::make('email')])->disabled(),
                    ])->visible(fn ($record, $context) => auth()->user()->can('viewSensitive', $record) && $context === 'view'),

                    Fieldset::make('State')->columnSpanFull()->schema([
                        Grid::make(3)->columnSpanFull()->schema([
                            Placeholder::make('vatsim_region')
                                ->label('VATSIM Region')
                                ->content(fn ($record) => $record->primary_permanent_state?->pivot?->region),
                            Placeholder::make('vatsim_division')
                                ->label('VATSIM Division')
                                ->content(fn ($record) => $record->primary_permanent_state?->pivot?->division),
                            Placeholder::make('uk_primary_state')
                                ->label('UK Primary State')
                                ->content(fn ($record) => $record->primary_state?->name),
                        ]),
                    ])->visibleOn('view'),

                    Fieldset::make('Qualifications')->columnSpanFull()->schema(function ($record) {
                        return [
                            Grid::make(3)
                                ->columnSpanFull()
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
                TextColumn::make('id')->sortable()->searchable()->label('CID'),
                TextColumn::make('discord_id')
                    ->searchable()
                    ->label('Discord ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')->sortable()->searchable(['name_first', 'name_last']),
                TextColumn::make('qualification_atc')->sortable()->label('ATC Rating'),
                TextColumn::make('qualification_pilot')->sortable()->label('Pilot Rating'),
                BadgeColumn::make('state')
                    ->getStateUsing(fn ($record) => $record->primary_state?->name)
                    ->colors([
                        'primary' => static fn ($state) => in_array($state, ['Division', 'Transferring']),
                        'secondary' => static fn ($state) => in_array($state, ['Visiting']),
                    ]),
                IconColumn::make('is_banned')->label('Banned')
                    ->boolean()
                    ->falseIcon('')
                    ->trueColor('danger')
                    ->tooltip(fn ($record) => $record->is_banned ? ($record->is_network_banned ? 'Banned on VATSIM.NET' : ('Banned locally for another '.now()->diffForHumans($record->system_ban->period_finish, CarbonInterface::DIFF_ABSOLUTE))) : null),
            ])
            ->filters([
                SelectFilter::make('state')
                    ->relationship('states', 'name')
                    ->label('State'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [
            StatesRelationManager::class,
            QualificationsRelationManager::class,
            FeedbackRelationManager::class,
            RolesRelationManager::class,
            BansRelationManager::class,
            NotesRelationManager::class,
            EndorsementsRelationManager::class,
            WaitingListsRelationManager::class,
            RetentionChecksRelationManager::class,
            VisitTransferRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'view' => ViewAccount::route('/{record}'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }

    /** Maps the account's active qualifications into a set of placeholder fields */
    private static function makeQualificationSummaryPlaceholders($record): array
    {
        return $record->active_qualifications->map(function ($qualification) {
            return Placeholder::make("qualification_{$qualification->type}")
                ->label(QualificationTypeEnum::from($qualification->type)->human())
                ->content("{$qualification->name_long} ({$qualification->code})");
        })->all();
    }
}
