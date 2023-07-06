<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\Filament\Resources\FeedbackResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;

class ViewFeedback extends ViewRecord
{
    protected static string $resource = FeedbackResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make("send_feedback")
                ->label("Send Feedback")
                ->action(fn ($data) => $this->record->markSent(auth()->user(), $data['comment']))
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10')
                ])
                ->hidden(fn () => $this->record->sent_at !== null),
            Actions\Action::make("action_feedback")
                ->label("Action Feedback")
                ->action(fn ($data) => $this->record->markActioned(auth()->user(), $data['comment']))
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10')
                ])
                ->hidden(fn () => $this->record->actioned_at !== null),
        ];
    }
}
