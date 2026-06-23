<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Discord\DiscordTagResource;
use App\Jobs\Discord\SyncDiscordTags;
use App\Models\Discord\DiscordTag;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;

class ListDiscordTags extends BaseListRecordsPage
{
    protected static string $resource = DiscordTagResource::class;

    private const TAG_LIMIT = 25;

    public function getSubheading(): ?string
    {
        $limit = self::TAG_LIMIT;
        $count = DiscordTag::count();
        $remaining = $limit - $count;

        return "Tags are synced automatically. Discord supports up to {$limit} tag choices ({$remaining} remaining).";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => DiscordTag::count() < self::TAG_LIMIT),
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
