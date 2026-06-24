<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\Discord\DiscordTagResource;
use Filament\Actions\DeleteAction;

class EditDiscordTag extends BaseEditRecordPage
{
    protected static string $resource = DiscordTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
