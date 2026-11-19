<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\Roles\RoleResource;
use Filament\Actions\EditAction;

class ViewRole extends BaseViewRecordPage
{
    protected static string $resource = RoleResource::class;

    public function mountCanAuthorizeResourceAccess(): void
    {
        // The resource check is viewAny, which is role.view.* and would show
        // every role. A delegated manager may open one role they manage.
        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    public function hydrateCanAuthorizeResourceAccess(): void
    {
        $this->mountCanAuthorizeResourceAccess();
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
