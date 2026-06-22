<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Discord\DiscordTagResource;
use App\Jobs\Discord\SyncDiscordTags;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListDiscordTags extends BaseListRecordsPage
{
    protected static string $resource = DiscordTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('sync')
                ->label('Sync to Discord')
                ->color('gray')
                ->action(function () {
                    SyncDiscordTags::dispatch();
                })
                ->successNotificationTitle('Tag sync has been queued.')
                ->authorize(fn () => auth()->user()->can('discord.tag.manage')),
        ];
    }
}
