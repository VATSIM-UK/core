<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Resources\AccountResource;
use App\Models\Contact;
use App\Models\Mship\Account;
use App\Notifications\Mship\UserImpersonated;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getActions(): array
    {
        return [
            Actions\Action::make('impersonate')
                ->visible(fn (Account $subject) => auth()->user()->can('impersonate', $subject))
                ->color('danger')
                ->modalHeading('Impersonate User')
                ->modalSubheading(new HtmlString('<p>Clicking confirm will log you in as this user on the user facing side and log you out of your own account.</p>

<p>This feature should only be used in rare and extreme circumstances. All impersonations are monitored, and may be followed up. Use of this feature must be authorized by the Web Services Director every time it is used.</p>

<p><strong>You MUST include the Helpdesk ticket reference in your reason.<strong></p>'))
                ->form([
                    Textarea::make('reason')->required()->minLength(10),
                ])
                ->action(function (array $data) {
                    Contact::where('key', 'PRIVACC')->first()
                        ->notify(new UserImpersonated($this->record, auth()->user(), $data['reason']));

                    // Let's do the login!
                    Auth::loginUsingId($this->record->id, false);
                    Session::put('auth_override', true);

                    Redirect::to(URL::route('mship.manage.dashboard'))->with('success', 'You are now impersonating this user - your reason has been logged. Be good!');
                }),
            Actions\EditAction::make(),
        ];
    }
}
