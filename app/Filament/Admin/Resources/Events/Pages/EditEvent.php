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
                ->modalHeading(fn (Event $record): string => $record->isPublished() ? 'Republish event' : 'Publish event')
                ->modalDescription(fn (Event $record): string => $record->isPublished()
                    ? 'This will update the published date and record you as the publisher. '.$this->publishDescription($record)
                    : $this->publishDescription($record)
                )
                ->successNotificationTitle(fn (Event $record): string => $record->isPublished() ? 'Event republished' : 'Event published')
                ->action(function (Event $record) {
                    app(EventService::class)->publish($record, auth()->user());
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
