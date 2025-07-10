<?php

namespace App\Filament\Admin\Resources\AccountResource\Pages;

use App\Events\Discord\DiscordUnlinked;
use App\Filament\Admin\Helpers\Pages\BaseViewRecordPage;
use App\Filament\Admin\Helpers\Pages\LogPageAccess;
use App\Filament\Admin\Resources\AccountResource;
use App\Jobs\UpdateMember;
use App\Models\Contact;
use App\Models\Mship\Note\Type;
use App\Models\Roster;
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
    use LogPageAccess;

    protected static string $resource = AccountResource::class;

    protected function getLogActionName(): string
    {
        return 'ViewAccount';
    }

    protected function getHeaderActions(): array
    {
        $roster = $this->record->roster;
        $onRoster = $this->record->onRoster();
        $hasRosterRestriction = (bool) $roster?->restrictionNote;

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
                Actions\Action::make('toggle_roster_status')
                    ->visible(fn () => auth()->user()->can('roster.manage'))
                    ->color($onRoster ? 'danger' : 'success')
                    ->icon('heroicon-o-list-bullet')
                    ->name($onRoster ? 'Remove from roster' : 'Add to roster')
                    ->modalHeading($onRoster ? 'Remove from roster' : 'Add to roster')
                    ->action(function () use ($onRoster) {
                        Roster::withoutGlobalScopes()->where('account_id', $this->record->id)->get()->each->remove();

                        if (! $onRoster) {
                            Roster::create(['account_id' => $this->record->id]);
                        }

                        $this->refreshFormData(['roster_status', 'notes']);
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Roster status updated!'),
                ...$this->getRosterRestrictionActions($roster, $onRoster, $hasRosterRestriction),
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
                Actions\Action::make('unlink_discord')
                    ->label('Unlink Discord')
                    ->visible(fn () => $this->record->discord_id && auth()->user()->can('unlinkDiscordAccount', $this->record))
                    ->color('warning')
                    ->icon('heroicon-o-link')
                    ->modalHeading('Unlink Discord Account')
                    ->action(function () {
                        event(new DiscordUnlinked($this->record));
                    })
                    ->requiresConfirmation()
                    ->successNotificationTitle('Discord account unlinked'),
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

    protected function getRosterRestrictionActions(?Roster $roster, bool $onRoster, bool $hasRosterRestriction): array
    {
        return [
            Actions\Action::make('roster_restriction')
                ->visible(fn () => auth()->user()->can('roster.restriction.create') && $onRoster)
                ->color('warning')
                ->icon('heroicon-o-exclamation-triangle')
                ->name($hasRosterRestriction ? 'Edit roster restriction' : 'Add roster restriction')
                ->modalHeading($hasRosterRestriction ? 'Edit Roster Restriction' : 'Add Roster Restriction')
                ->form([
                    Textarea::make('restriction_note')
                        ->label('Restriction Note')
                        ->required()
                        ->default(fn () => $this->record->roster?->restrictionNote?->content ?? '')
                        ->helperText('This note will be displayed on the roster and in the user\'s profile on record so please ensure it is appropriate for public display.'),
                ])
                ->action(function (array $data) use ($roster) {
                    $note = $roster->account->addNote(Type::isShortCode('roster')->first(), $data['restriction_note'], auth()->user());
                    $roster->restriction_note_id = $note->id;
                    $roster->save();

                    $this->dispatch('refreshNotes');
                })
                ->requiresConfirmation(),

            Actions\Action::make('roster_restriction_remove')
                ->visible(fn () => $hasRosterRestriction && auth()->user()->can('roster.restriction.remove'))
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->modalHeading('Remove Roster Restriction')
                ->form([
                    Textarea::make('restriction_removal_note')
                        ->label('Removal Note')
                        ->required()
                        ->helperText('This note will be displayed on the members profile on record.'),
                ])
                ->action(function ($data) use ($roster) {
                    $roster->account->addNote(Type::isShortCode('roster')->first(), $data['restriction_removal_note'], auth()->user());
                    $roster->restriction_note_id = null;
                    $roster->save();

                    $this->dispatch('refreshNotes');
                })
                ->requiresConfirmation()
                ->successNotificationTitle('Roster restriction removed'),
        ];
    }
}
