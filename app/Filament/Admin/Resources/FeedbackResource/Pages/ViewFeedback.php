<?php

namespace App\Filament\Admin\Resources\FeedbackResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\FeedbackResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;

class ViewFeedback extends BaseViewRecordPage
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_feedback')
                ->label('Send Feedback')
                ->action(fn ($data) => $this->record->markSent(auth()->user(), $data['comment']))
                ->schema([
                    Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10'),
                ])
                ->visible(fn () => $this->record->sent_at === null && auth()->user()->can('actionFeedback', $this->record)),
            Action::make('action_feedback')
                ->label('Action Feedback')
                ->action(fn ($data) => $this->record->markActioned(auth()->user(), $data['comment']))
                ->schema([
                    Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10'),
                ])
                ->visible(fn () => $this->record->actioned_at === null && auth()->user()->can('actionFeedback', $this->record)),
        ];
    }
}
