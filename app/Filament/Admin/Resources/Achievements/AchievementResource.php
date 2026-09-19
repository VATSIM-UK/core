<?php

namespace App\Filament\Admin\Resources\Achievements;

use App\Filament\Admin\Resources\Achievements\Pages\AwardedMembers;
use App\Filament\Admin\Resources\Achievements\Pages\CreateAchievement;
use App\Filament\Admin\Resources\Achievements\Pages\EditAchievement;
use App\Filament\Admin\Resources\Achievements\Pages\ListAchievements;
use App\Models\Mship\Achievement;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Trophy;

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    public static function canViewAny(): bool
    {
        return auth()->user()->canAny(['achievements.view', 'achievements.manage']);
    }

    public static function canView($record): bool
    {
        return auth()->user()->canAny(['achievements.view', 'achievements.manage']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('achievements.manage');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->can('achievements.manage');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->can('achievements.manage');
    }

    public static function canAward(): bool
    {
        return auth()->user()->can('achievements.award');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAchievements::route('/'),
            'create' => CreateAchievement::route('/create'),
            'edit' => EditAchievement::route('/{record}/edit'),
            'awarded-members' => AwardedMembers::route('/{record}/awarded-members'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->maxLength(50)
                    ->columnSpanFull()
                    ->autofocus()
                    ->required(),
                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(100)
                    ->columnSpanFull()
                    ->autosize()
                    ->required(),
                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->maxSize(1024)
                    ->automaticallyResizeImagesToWidth(64)
                    ->automaticallyResizeImagesToHeight(64)
                    ->directory('achievements')
                    ->visibility('public')
                    ->columnSpanFull()
                    ->required(),
                TextEntry::make('created_by')->state(fn (?Achievement $record): ?string => $record?->creator?->name ?? '-')
                    ->label('Created By'),
                TextEntry::make('created_at')->state(fn (?Achievement $record): ?string => $record?->created_at?->toPanelDateTime() ?? '-')
                    ->label('Created At'),
                TextEntry::make('updated_by')->state(fn (?Achievement $record): ?string => $record?->updater?->name ?? '-')
                    ->label('Updated By'),
                TextEntry::make('updated_at')->state(fn (?Achievement $record): ?string => $record?->updated_at?->toPanelDateTime() ?? '-')
                    ->label('Updated At'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name'),
                TextColumn::make('description')
                    ->label('Description')->wrap()->limit(80),
                TextColumn::make('awards_count')
                    ->label('Awarded')
                    ->counts('awards')->badge(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime(),
                ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public'),
            ])->actions([
                EditAction::make()->color('warning')->visible(fn (Achievement $record): bool => AchievementResource::canEdit($record)),
                Action::make('awardedMembers')
                    ->label('Awarded')
                    ->icon(Heroicon::Users)
                    ->url(fn (Achievement $record): string => AchievementResource::getUrl('awarded-members', ['record' => $record])),
            ])->recordUrl(false)->defaultSort('name', 'asc');
    }
}
