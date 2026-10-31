<?php

namespace App\Filament\Admin\Resources\Roles\Pages;

use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\Roles\Concerns\SyncsRoleStaffPosition;
use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Models\Mship\RoleStaffPosition;
use Filament\Actions\DeleteAction;

class EditRole extends BaseEditRecordPage
{
    use SyncsRoleStaffPosition;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data = parent::mutateFormDataBeforeFill($data);

        $position = RoleStaffPosition::query()
            ->where('role_id', $this->getRecord()->getKey())
            ->first();

        $data['staff_department'] = $position?->department;
        $data['staff_job_title'] = $position?->job_title;
        $data['staff_is_department_lead'] = (bool) $position?->is_department_lead;
        $data['staff_sort_order'] = $position?->sort_order ?? 0;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->syncRoleStaffPosition();
    }
}
