<?php

namespace App\Filament\Admin\Resources\Roles;

use App\Filament\Admin\Resources\Roles\Pages\CreateRole;
use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Admin\Resources\Roles\Pages\ListRoles;
use App\Filament\Admin\Resources\Roles\RelationManagers\DelegatesRelationManager;
use App\Filament\Admin\Resources\Roles\RelationManagers\UsersRelationManager;
use App\Jobs\UpdateMember;
use DB;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
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
                CheckboxList::make('permissions')
                    ->relationship('permissions', 'name')
                    ->columns(3)
                    ->searchable()
                    ->bulkToggleable()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('users_count')->counts('users')->label('Assigned Users')->badge(),
            ])
            ->recordActions([
                Action::make('syncDiscord')
                    ->label('Sync Discord')
                    ->visible(fn () => auth()->user()?->can('role.sync-discord.*'))
                    ->action(function (Role $record) {
                        // Get all user IDs for this role
                        $userIds = DB::table('mship_account_role')
                            ->where('role_id', $record->id)
                            ->pluck('model_id');
                        foreach ($userIds as $userId) {
                            UpdateMember::dispatch($userId);
                        }
                        Notification::make()
                            ->title('Central details refresh & service sync queued for all users with this role.')
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation(),
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->visible(fn () => auth()->user()?->can('role.delete.*')),
                BulkAction::make('assignPermissions')
                    ->icon('heroicon-o-shield-check')
                    ->label('Assign Permissions')
                    ->visible(fn () => auth()->user()?->can('role.edit.*'))
                    ->form([
                        CheckboxList::make('permissions')
                            ->options(Permission::orderBy('name')->pluck('name', 'name'))
                            ->columns(3)
                            ->searchable()
                            ->bulkToggleable()
                            ->required(),
                    ])
                    ->action(function ($records, array $data) {
                        foreach ($records as $role) {
                            $role->givePermissionTo($data['permissions']);
                        }
                        Notification::make()
                            ->title('Permissions updated for selected roles.')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UsersRelationManager::class,
            DelegatesRelationManager::class,
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
