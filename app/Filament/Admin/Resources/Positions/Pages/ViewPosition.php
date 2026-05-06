<?php

namespace App\Filament\Admin\Resources\Positions\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\Positions\PositionResource;
use Filament\Actions\Action;

class ViewPosition extends BaseViewRecordPage
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->modal()
                ->modalHeading('Edit Position')
                ->modalSubmitActionLabel('Save')
                ->form(PositionResource::getFormSchema())
                ->fillForm(fn (): array => $this->record->toArray())
                ->action(function (array $data): void {
                    $this->record->update($data);
                }),
        ];
    }
}
