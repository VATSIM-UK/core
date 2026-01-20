<?php

namespace App\Filament\Admin\Resources\FeedbackResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Filament\Admin\Resources\FeedbackResource;
use Filament\Actions;
use Filament\Forms;

class ViewFeedback extends BaseViewRecordPage
{
    protected static string $resource = FeedbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('send_feedback')
                ->label('Send Feedback')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->action(fn ($data) => $this->record->markSent(auth()->user(), $data['comment']))
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10'),
                ])
                ->visible(fn () => $this->record->sent_at === null && auth()->user()->can('actionFeedback', $this->record)),

            Actions\Action::make('action_feedback')
                ->label('Action Feedback')
                ->color('info')
                ->icon('heroicon-o-check-circle')
                ->action(fn ($data) => $this->record->markActioned(auth()->user(), $data['comment']))
                ->form([
                    Forms\Components\Textarea::make('comment')
                        ->label('Comment')
                        ->rules('required', 'min:10'),
                ])
                ->visible(fn () => $this->record->actioned_at === null && auth()->user()->can('actionFeedback', $this->record)),
            
            Actions\Action::make('re_allocate_feedback')
                ->label('Re-allocate Feedback')
                ->color('gray')
                ->icon('heroicon-o-arrow-right')
                ->action(fn ($data) => $this->record->reallocate($data['account_id']))
                ->form([
                    AccountSelect::make('account')
                        ->label('Account')
                        ->required()
                ])
                ->visible(fn () => $this->record->actioned_at === null && $this->record->sent_at === null && auth()->user()->can('actionFeedback', $this->record)),

            Actions\Action::make('reject_feedback')
                ->label('Reject Feedback')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->action(fn () => $this->record->markRejected(auth()->user()))
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->can('actionFeedback', $this->record)),
        ];
    }
}
