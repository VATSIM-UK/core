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
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                Fieldset::make('Basic Details')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nickname')
                            ->label('Preferred Name'),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Details')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->getStateUsing(fn (Account $record) => $record->name_first.' '.$record->name_last),
                        TextEntry::make('nickname')
                            ->label('Preferred Name'),
                        TextEntry::make('id')
                            ->label('CID')
                            ->copyable(),

                        TextEntry::make('email')
                            ->label('Primary Email')
                            ->copyable()
                            ->visible(fn (Account $record) => auth()->user()->can('viewSensitive', $record)),
                        TextEntry::make('secondaryEmails.email')
                            ->label('Secondary Emails')
                            ->listWithLineBreaks()
                            ->copyable()
                            ->visible(fn (Account $record) => auth()->user()->can('viewSensitive', $record)),

                        TextEntry::make('has_secondary_password')
                            ->label('Has Secondary Password')
                            ->getStateUsing(fn (Account $record) => $record->hasPassword() ? 'Yes' : 'No'),
                        TextEntry::make('discord_id')
                            ->label('Discord ID')
                            ->getStateUsing(fn (Account $record) => $record->discord_id ?? 'Not Linked'),
                        TextEntry::make('roster_status')
                            ->label('Roster Status')
                            ->badge()
                            ->getStateUsing(fn (Account $record) => Roster::where('account_id', $record->id)->exists() ? 'Active' : 'Inactive')
                            ->icon(fn (string $state): string => $state === 'Active' ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color(fn (string $state): string => $state === 'Active' ? 'success' : 'danger'),
                        TextEntry::make('last_seen_controlling_uk')
                            ->label('Last UK Controlling Session')
                            ->getStateUsing(fn (Account $record) => $record->lastSeenControllingUK()?->format('d M Y, H:i') ?? 'Never Controlled'),
                    ]),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        Section::make('State')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('vatsim_region')
                                    ->label('VATSIM Region')
                                    ->getStateUsing(fn (Account $record) => $record->primary_permanent_state?->pivot?->region),
                                TextEntry::make('vatsim_division')
                                    ->label('VATSIM Division')
                                    ->getStateUsing(fn (Account $record) => $record->primary_permanent_state?->pivot?->division),
                                TextEntry::make('uk_primary_state')
                                    ->label('UK Primary State')
                                    ->getStateUsing(fn (Account $record) => $record->primary_state?->name),
                            ]),

                        Section::make('Qualifications')
                            ->columns(1)
                            ->schema(fn (Account $record) => static::makeQualificationSummaryEntries($record)),
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
                TextColumn::make('state')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->primary_state?->name)
                    ->color(static fn (?string $state): ?string => match (true) {
                        in_array($state, ['Division', 'Transferring'], true) => 'primary',
                        in_array($state, ['Visiting'], true) => 'secondary',
                        default => null,
                    }),
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
    private static function makeQualificationSummaryEntries(Account $record): array
    {
        return $record->active_qualifications
            ->map(function ($qualification) {
                return TextEntry::make("qualification_{$qualification->type}")
                    ->label(QualificationTypeEnum::from($qualification->type)->human())
                    ->state("{$qualification->name_long} ({$qualification->code})");
            })
            ->all();
    }
}
