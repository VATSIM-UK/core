<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Discord\DiscordRoleRuleResource;
use App\Libraries\Discord;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;

class ListDiscordRoleRules extends BaseListRecordsPage
{
    protected static string $resource = DiscordRoleRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('refreshDiscordRoles')
                ->label('Refresh from Discord')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->authorize(fn () => auth()->user()?->can('discord.role-rule.edit') ?? false)
                ->requiresConfirmation()
                ->modalDescription('Discard the cached Discord role list and fetch it again.')
                ->action(function () {
                    $discord = app(Discord::class);
                    $discord->forgetGuildRolesCache();
                    $count = $discord->getGuildRoles()->count();

                    if ($count === 0) {
                        Notification::make()
                            ->title('No Discord roles were returned - the request may have failed.')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Fetched {$count} Discord role(s).")
                        ->success()
                        ->send();
                }),
        ];
    }
}
