<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Discord\DiscordTagResource;
use Filament\Actions\CreateAction;

class ListDiscordTags extends BaseListRecordsPage
{
    protected static string $resource = DiscordTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
