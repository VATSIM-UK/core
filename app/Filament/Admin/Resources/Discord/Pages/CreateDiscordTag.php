<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Resources\Discord\DiscordTagResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscordTag extends CreateRecord
{
    protected static string $resource = DiscordTagResource::class;
}
