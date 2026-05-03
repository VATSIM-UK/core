<?php

namespace App\Filament\Admin\Resources\Feedback\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\Feedback\FeedbackResource;
use Filament\Actions\Action;

class ViewFeedback extends BaseViewRecordPage
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        $sendConfig = FeedbackResource::sendFeedbackConfig();
        $actionConfig = FeedbackResource::actionFeedbackConfig();
        $rejectConfig = FeedbackResource::rejectFeedbackConfig();
        $reallocateConfig = FeedbackResource::reallocateFeedbackConfig();

        return [
            Action::make('send_feedback')
                ->label($sendConfig['label'])
                ->color($sendConfig['color'])
                ->icon($sendConfig['icon'])
                ->action(fn ($data) => $this->record->markSent(auth()->user(), $data['comment']))
                ->schema($sendConfig['form'])
                ->visible(fn () => $this->record->sent_at === null && ! $this->record->trashed() && auth()->user()->can('actionFeedback', $this->record)),
            Action::make('action_feedback')
                ->label($actionConfig['label'])
                ->color($actionConfig['color'])
                ->icon($actionConfig['icon'])
                ->action(fn ($data) => $this->record->markActioned(auth()->user(), $data['comment']))
                ->schema($actionConfig['form'])
                ->visible(fn () => $this->record->actioned_at === null && ! $this->record->trashed() && auth()->user()->can('actionFeedback', $this->record)),

            Action::make('reject_feedback')
                ->label($rejectConfig['label'])
                ->color($rejectConfig['color'])
                ->icon($rejectConfig['icon'])
                ->action(fn ($data) => $this->record->markRejected(auth()->user(), $data['reason']))
                ->schema($rejectConfig['form'])
                ->visible(fn () => ! $this->record->trashed() && auth()->user()->can('actionFeedback', $this->record)),

            Action::make('reallocate_feedback')
                ->label($reallocateConfig['label'])
                ->color($reallocateConfig['color'])
                ->icon($reallocateConfig['icon'])
                ->action(fn ($data) => $this->record->reallocate($data['account_id']))
                ->modalSubmitActionLabel('Reallocate')
                ->schema($reallocateConfig['form'])
                ->visible(fn () => $this->record->actioned_at === null && $this->record->sent_at === null && auth()->user()->can('actionFeedback', $this->record)),
        ];
    }
}
