<?php

namespace App\Http\Controllers\Adm\Mship\Account;

use App\Http\Controllers\Adm\AdmController;
use App\Jobs\UpdateMember;
use App\Models\Contact;
use App\Models\Mship\Account as AccountData;
use App\Models\Mship\Note\Type as NoteTypeData;
use App\Notifications\Mship\UserImpersonated;
use Auth;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Redirect;
use Session;
use URL;

class Settings extends AdmController
{
    /*
     * Notes
     */

    public function postNoteCreate(AccountData $mshipAccount)
    {
        if (! $mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Is there any content?
        if (strlen(Request::input('content')) < 10) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
                ->withError('You cannot add such a short note!');
        }

        // Check this type exists!
        $noteType = NoteTypeData::find(Request::input('note_type_id'));
        if (! $noteType or ! $noteType->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
                ->withError('You selected an invalid note type.');
        }

        // Let's make a note and attach it to the user!
        $mshipAccount->addNote($noteType, Request::input('content'), Auth::user());

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
            ->withSuccess('The note has been saved successfully!');
    }

    /*
     * Troubleshooting
     */

    public function postImpersonate(HttpRequest $request, AccountData $mshipAccount)
    {
        if (! $mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        $attributes = $this->validate($request, [
            'reason' => 'required|string|min:5',
        ]);

        Contact::where('key', 'PRIVACC')->first()
            ->notify(new UserImpersonated($mshipAccount, $request->user(), $attributes['reason']));

        // Let's do the login!
        Auth::loginUsingId($mshipAccount->id, false);
        Session::put('auth_override', true);

        return Redirect::to(URL::route('mship.manage.dashboard'))
            ->withSuccess('You are now impersonating this user - your reason has been logged. Be good!');
    }

    public function sync(AccountData $mshipAccount)
    {
        if (! $mshipAccount) {
            return Redirect::route('adm.mship.account.index')
                ->withError('This user does not exist');
        }

        UpdateMember::dispatch($mshipAccount->id);

        return Redirect::back()
            ->withSuccess('User queued to refresh central membership details & sync to external services!');
    }
}
