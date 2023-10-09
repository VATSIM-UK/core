<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\Filament\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Resources\FeedbackResource;
use Filament\Forms;
use Filament\Pages\Actions;

class ViewFeedback extends BaseViewRecordPage
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_feedback')
                ->label('Send Feedback')
                ->action(fn ($data) => $this->record->markSent(auth()->user(), $data['comment']))
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10'),
                ])
                ->visible(fn () => $this->record->sent_at === null && auth()->user()->can('actionFeedback', $this->record)),
            Actions\Action::make('action_feedback')
                ->label('Action Feedback')
                ->action(fn ($data) => $this->record->markActioned(auth()->user(), $data['comment']))
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10'),
                ])
                ->visible(fn () => $this->record->actioned_at === null && auth()->user()->can('actionFeedback', $this->record)),
        ];
    }
}
