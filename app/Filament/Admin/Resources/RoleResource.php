<?php

namespace App\Filament\Admin\Resources;

use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'User Management';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->unique(ignorable: fn ($record) => $record),
                Forms\Components\TextInput::make('guard_name')->default('web')->in(array_keys(config('auth.guards'))),
                Grid::make(1)->schema([
                    CheckboxList::make('permissions')->relationship('permissions', 'name')->columns(3)->searchable()->bulkToggleable(),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\BadgeColumn::make('users_count')->counts('users')->label('Assigned Users'),
            ])
            ->actions([
                Tables\Actions\Action::make('syncDiscord')
                    ->label('Sync Discord')
                    ->visible(fn () => auth()->user()?->can('syncDiscord'))
                    ->action(function (Role $record) {
                        // Get all user IDs for this role
                        $userIds = \DB::table('mship_account_role')
                            ->where('role_id', $record->id)
                            ->pluck('model_id');
                        foreach ($userIds as $userId) {
                            \App\Jobs\UpdateMember::dispatch($userId);
                        }
                        filament()->notify('success', 'Central details refresh & service sync queued for all users with this role.');
                    })
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\RoleResource\RelationManagers\UsersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Admin\Resources\RoleResource\Pages\ListRoles::route('/'),
            'create' => \App\Filament\Admin\Resources\RoleResource\Pages\CreateRole::route('/create'),
            'edit' => \App\Filament\Admin\Resources\RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
