<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Resources\Events\EventResource;
use App\Models\Events\Event;
use App\Services\Events\EventService;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label(fn (Event $record): string => $record->isPublished() ? 'Republish' : 'Publish')
                ->requiresConfirmation()
                ->modalHeading('Publish event')
                ->modalDescription(fn (Event $record): string => $record->isPublished()
                    ? 'This event is already published.'
                    : $this->publishDescription($record)
                )
                ->successNotificationTitle('Event published')
                ->action(function (Event $record) {
                    app(EventService::class)->publish($record);
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $record]));
                }),
        ];
    }

    private function publishDescription(Event $record): string
    {
        $incomplete = $record->unpublishedChecklist();

        if (empty($incomplete)) {
            return 'All prep steps are complete. Publish this event?';
        }

        return 'The following prep steps are not complete: '.implode(', ', $incomplete).'. Publish anyway?';
    }
}
