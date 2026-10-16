<?php

namespace App\Filament\Admin\Resources\Discord;

use App\Filament\Admin\Resources\Discord\Pages\CreateDiscordRoleRule;
use App\Filament\Admin\Resources\Discord\Pages\EditDiscordRoleRule;
use App\Filament\Admin\Resources\Discord\Pages\ListDiscordRoleRules;
use App\Jobs\Discord\SyncDiscordRoleRuleMembers;
use App\Libraries\Discord;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Discord\DiscordRoleRule;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class DiscordRoleRuleResource extends Resource
{
    protected static ?string $model = DiscordRoleRule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static string|\UnitEnum|null $navigationGroup = 'Discord';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('A descriptive name for this rule.'),
                TextInput::make('discord_id')
                    ->label('Discord role ID')
                    ->required()
                    ->rule('digits_between:1,20')
                    ->helperText('The Discord role snowflake this rule manages.'),
                Select::make('permission_id')
                    ->label('Requires permission')
                    ->options(fn () => Permission::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('qualification_id')
                    ->label('Requires qualification')
                    ->options(fn () => Qualification::query()->orderBy('code')->pluck('code', 'id'))
                    ->searchable()
                    ->nullable(),
                Select::make('state_id')
                    ->label('Requires membership state')
                    ->options(fn () => State::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                TextInput::make('cts_may_control_contains')
                    ->label('CTS may-control contains')
                    ->maxLength(255)
                    ->nullable()
                    ->helperText('Only apply if the member CTS may-control string contains this value.'),
                Select::make('endorsable_type')
                    ->label('Requires endorsement on')
                    ->options([
                        Position::class => 'Position',
                        PositionGroup::class => 'Position group',
                    ])
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('endorsable_id', null))
                    ->nullable(),
                Select::make('endorsable_id')
                    ->label('Endorsed position')
                    ->options(function (callable $get) {
                        return match ($get('endorsable_type')) {
                            Position::class => Position::query()->orderBy('callsign')->pluck('callsign', 'id')->all(),
                            PositionGroup::class => PositionGroup::query()->orderBy('name')->pluck('name', 'id')->all(),
                            default => [],
                        };
                    })
                    ->searchable()
                    ->required(fn (callable $get) => filled($get('endorsable_type')))
                    ->nullable()
                    ->visible(fn (callable $get) => filled($get('endorsable_type'))),
                Select::make('position_type')
                    ->label('Solo endorsement position type')
                    ->options(Position::typeOptions())
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $guildRoles = app(Discord::class)->getGuildRoles();

        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('discord_id')
                    ->label('Discord role')
                    ->html()
                    ->formatStateUsing(fn ($state) => static::discordRoleLabel($guildRoles, (string) $state)),
                TextColumn::make('permission.name')
                    ->label('Permission')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('qualification.code')
                    ->label('Qualification')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('state.name')
                    ->label('State')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('position_type')
                    ->label('Position type')
                    ->formatStateUsing(fn ($state) => $state === null ? null : (Position::typeOptions()[$state] ?? $state))
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('cts_may_control_contains')
                    ->label('CTS may-control contains')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make(),
                static::resyncAction(),
                DeleteAction::make()
                    ->modalDescription('Deleting a rule stops it managing the Discord role. Members who already have the role keep it, because an unmanaged role is no longer reconciled.'),
            ])
            ->defaultSort('name');
    }

    public static function resyncAction(): Action
    {
        return Action::make('resync')
            ->label('Re-sync affected members')
            ->icon('heroicon-o-arrow-path-rounded-square')
            ->color('gray')
            ->authorize(fn () => auth()->user()?->can('discord.role-rule.edit') ?? false)
            ->requiresConfirmation()
            ->modalDescription('Re-sync every Discord-linked member that currently satisfies this rule. This runs in the background.')
            ->action(function (DiscordRoleRule $record) {
                SyncDiscordRoleRuleMembers::dispatch($record->getKey());

                Notification::make()
                    ->title('Discord re-sync queued.')
                    ->success()
                    ->send();
            });
    }

    public static function discordRoleLabel(Collection $guildRoles, string $discordId): string
    {
        $role = $guildRoles->first(fn ($role) => is_array($role) && (int) ($role['id'] ?? 0) === (int) $discordId);

        $idLine = sprintf('<span style="font-size:0.75rem;color:#6b7280">%s</span>', e($discordId));

        if (! $role) {
            return sprintf(
                '<span style="display:inline-flex;flex-direction:column;gap:0.15rem;line-height:1.15">'
                .'<span style="display:inline-flex;align-items:center;gap:0.4rem;color:#dc2626;font-weight:600">'
                .'<span>Not found on Discord</span></span>%s</span>',
                $idLine,
            );
        }

        return sprintf(
            '<span style="display:inline-flex;flex-direction:column;gap:0.15rem;line-height:1.15">'
            .'<span style="display:inline-flex;align-items:center;gap:0.5rem">'
            .'<span style="display:inline-block;width:0.85rem;height:0.85rem;border-radius:9999px;background:%s;border:1px solid rgba(0,0,0,0.15)"></span>'
            .'<span>%s</span></span>%s</span>',
            static::discordColourHex((int) ($role['color'] ?? 0)),
            e((string) ($role['name'] ?? $discordId)),
            $idLine,
        );
    }

    public static function discordColourHex(int $colour): string
    {
        if ($colour <= 0) {
            return '#99aab5';
        }

        return sprintf('#%06x', $colour);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscordRoleRules::route('/'),
            'create' => CreateDiscordRoleRule::route('/create'),
            'edit' => EditDiscordRoleRule::route('/{record}/edit'),
        ];
    }
}
