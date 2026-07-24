<?php

namespace App\Filament\Training\Resources\Seminars\Pages;

use App\Filament\Training\Resources\Seminars\SeminarResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateSeminar extends CreateRecord
{
    protected static string $resource = SeminarResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->hidden();
    }
}
