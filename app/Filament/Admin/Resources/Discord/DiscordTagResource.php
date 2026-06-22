<?php

namespace App\Filament\Admin\Resources\Discord;

use App\Filament\Admin\Resources\Discord\Pages\CreateDiscordTag;
use App\Filament\Admin\Resources\Discord\Pages\ListDiscordTags;
use App\Filament\Admin\Resources\Discord\Pages\ViewDiscordTag;
use App\Models\Discord\DiscordTag;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiscordTagResource extends Resource
{
    protected static ?string $model = DiscordTag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|\UnitEnum|null $navigationGroup = 'Discord';

    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->user()->canAny(['discord.tag.view', 'discord.tag.manage']);
    }

    public static function canView($record): bool
    {
        return auth()->user()->canAny(['discord.tag.view', 'discord.tag.manage']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('discord.tag.manage');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('discord.tag.manage');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('discord.tag.manage');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key')
                    ->required()
                    ->unique(ignorable: fn ($record) => $record)
                    ->maxLength(255)
                    ->helperText('Key used for the /tag <key> command'),
                TextInput::make('title')
                    ->maxLength(255),
                Textarea::make('value')
                    ->required()
                    ->rows(5)
                    ->helperText('Supports Markdown formatting'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('value')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->defaultSort('key');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDiscordTags::route('/'),
            'create' => CreateDiscordTag::route('/create'),
            'view' => ViewDiscordTag::route('/{record}'),
        ];
    }
}
