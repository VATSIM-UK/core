<?php

namespace App\Filament\Admin\Resources\Discord\Pages;

use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\Discord\DiscordRoleRuleResource;
use Filament\Actions\DeleteAction;

class EditDiscordRoleRule extends BaseEditRecordPage
{
    protected static string $resource = DiscordRoleRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->modalDescription('Deleting this rule also removes the Discord role from every member who currently has it. This runs in the background.'),
        ];
    }
}
