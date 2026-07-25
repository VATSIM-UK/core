<?php

namespace App\Filament\Training\Resources\EndorsementRequests\Pages;

use App\Events\Training\EndorsementRequestApproved;
use App\Filament\Training\Resources\EndorsementRequests\EndorsementRequestResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateEndorsementRequest extends CreateRecord
{
    protected static string $resource = EndorsementRequestResource::class;

    protected ?array $approvalData = null;

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(),
            $this->getCreateAndApproveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCreateAndApproveFormAction(): Action
    {
        return Action::make('createAndApprove')
            ->label('Create and Approve')
            ->modalHeading('Approve')
            ->modalSubmitActionLabel('Approve')
            ->color('success')
            ->schema(EndorsementRequestResource::approvalSchema())
            ->action(function (array $data): void {
                $this->approvalData = $data;

                $this->create();
            });
    }

    protected function afterCreate(): void
    {
        if ($this->approvalData === null) {
            return;
        }

        event(new EndorsementRequestApproved($this->record, $this->approvalData['days'] ?? null));

        Notification::make()
            ->title('Endorsement request created')
            ->success()
            ->send();

        $this->approvalData = null;
    }
}
