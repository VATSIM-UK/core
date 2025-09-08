<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Admin\Resources\RoleResource\Pages\EditRole;
use App\Filament\Admin\Resources\RoleResource\Pages\ListRoles;
use App\Filament\Admin\Resources\RoleResource\RelationManagers\UsersRelationManager;
use App\Jobs\UpdateMember;
use DB;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-swatch';

    protected static string|\UnitEnum|null $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required()->unique(ignorable: fn ($record) => $record),
                TextInput::make('guard_name')->default('web')->in(array_keys(config('auth.guards'))),
                Grid::make(1)->schema([
                    CheckboxList::make('permissions')->relationship('permissions', 'name')->columns(3)->searchable()->bulkToggleable(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                BadgeColumn::make('users_count')->counts('users')->label('Assigned Users'),
            ])
            ->recordActions([
                Action::make('syncDiscord')
                    ->label('Sync Discord')
                    ->visible(fn () => auth()->user()?->can('syncDiscord'))
                    ->action(function (Role $record) {
                        // Get all user IDs for this role
                        $userIds = DB::table('mship_account_role')
                            ->where('role_id', $record->id)
                            ->pluck('model_id');
                        foreach ($userIds as $userId) {
                            UpdateMember::dispatch($userId);
                        }
                        filament()->notify('success', 'Central details refresh & service sync queued for all users with this role.');
                    })
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
