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
                ->modalDescription('Deleting a rule stops it managing the Discord role. Members who already have the role keep it, because an unmanaged role is no longer reconciled.'),
        ];
    }
}
