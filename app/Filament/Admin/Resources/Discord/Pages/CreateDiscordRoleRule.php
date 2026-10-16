<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Resources\Discord\DiscordRoleRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscordRoleRule extends CreateRecord
{
    protected static string $resource = DiscordRoleRuleResource::class;
}
