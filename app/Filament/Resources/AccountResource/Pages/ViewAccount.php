<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Resources\AccountResource;
use App\Jobs\UpdateMember;
use App\Models\Contact;
use App\Notifications\Mship\UserImpersonated;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class ViewAccount extends BaseViewRecordPage
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\Action::make('request_central_update')
                ->color('gray')
                ->icon('heroicon-o-cloud-arrow-down')
                ->action(function ($action) {
                    UpdateMember::dispatch($this->record->id);
                    $action->success();
                })
                ->successNotificationTitle('Central details refresh & service sync queued'),

            ActionGroup::make([
                $this->getImpersonateAction(),
                Actions\Action::make('remove_password')
                    ->visible(fn () => $this->record->hasPassword() && auth()->user()->can('removeSecondaryPassword', $this->record))
                    ->color('warning')
                    ->icon('heroicon-o-key')
                    ->modalHeading('Remove Secondary Password')
                    ->action(function () {
                        $this->record->removePassword();
                        $this->refreshFormData(['has_secondary_password']);
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Password removed'),
                Actions\EditAction::make()->visible(auth()->user()->can('update', $this->record)),
            ]),
        ];
    }

    protected function getImpersonateAction(): Actions\Action
    {
        return Actions\Action::make('impersonate')
            ->visible(fn () => auth()->user()->can('impersonate', $this->record))
            ->icon('heroicon-o-finger-print')
            ->color('danger')
            ->modalHeading('Impersonate User')
            ->modalDescription(new HtmlString('<p>Clicking confirm will log you in as this user on the user facing side and log you out of your own account.</p>

<p>This feature should only be used in rare and extreme circumstances. All impersonations are monitored, and may be followed up. Use of this feature must be authorized by the Web Services Director every time it is used.</p>

<p><strong>You MUST include the Helpdesk ticket reference in your reason.<strong></p>'))
            ->form([
                Textarea::make('reason')->required()->minLength(10),
            ])
            ->modalSubmitActionLabel('Impersonate')
            ->action(function (array $data) {
                // Notify Privellged users that a user has been impersonated
                Contact::where('key', 'PRIVACC')->first()
                    ->notify(new UserImpersonated($this->record, auth()->user(), $data['reason']));

                // Let's do the login!
                Auth::loginUsingId($this->record->id, false);
                Session::put('auth_override', true);

                Redirect::to(URL::route('mship.manage.dashboard'))->with('success', 'You are now impersonating this user - your reason has been logged. Be good!');
            });
    }
}
