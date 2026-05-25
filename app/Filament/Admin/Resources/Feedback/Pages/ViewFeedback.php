<?php

namespace App\Filament\Admin\Resources\Feedback\Pages;

use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Resources\Feedback\FeedbackResource;
use Filament\Actions\Action;

class ViewFeedback extends BaseViewRecordPage
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_feedback')
                ->label('Send Feedback')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->action(fn ($data) => $this->record->markSent(auth()->user(), $data['comment']))
                ->schema([FeedbackResource::commentField()])
                ->visible(fn () => $this->record->sent_at === null && $this->isActionable()),

            Action::make('action_feedback')
                ->label('Action Feedback')
                ->color('info')
                ->icon('heroicon-o-check-circle')
                ->action(fn ($data) => $this->record->markActioned(auth()->user(), $data['comment']))
                ->schema([FeedbackResource::commentField()])
                ->visible(fn () => $this->record->actioned_at === null && $this->isActionable()),

            Action::make('reject_feedback')
                ->label('Reject Feedback')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->action(fn ($data) => $this->record->markRejected(auth()->user(), $data['reason']))
                ->schema([FeedbackResource::reasonField()])
                ->visible(fn () => $this->isActionable()),

            Action::make('reallocate_feedback')
                ->label('Reallocate feedback')
                ->color('gray')
                ->icon('heroicon-o-arrow-right')
                ->action(fn ($data) => $this->record->reallocate($data['account_id']))
                ->modalSubmitActionLabel('Reallocate')
                ->schema([
                    AccountSelect::make('account')
                        ->label('Account')
                        ->helperText('Select the account you want to reallocate the feedback to.')
                        ->required(),
                ])
                ->visible(fn () => $this->record->actioned_at === null && $this->record->sent_at === null && $this->isActionable()),
        ];
    }

    private function isActionable(): bool
    {
        return ! $this->record->trashed() && auth()->user()->can('actionFeedback', $this->record);
    }
}
