<?php

namespace App\Http\Controllers\Adm\Mship\Account;

use App\Events\Mship\AccountAltered;
use App\Http\Controllers\Adm\AdmController;
use App\Listeners\Mship\SyncSubscriber;
use App\Models\Contact;
use App\Models\Mship\Account as AccountData;
use App\Models\Mship\Note\Type as NoteTypeData;
use App\Notifications\Mship\UserImpersonated;
use Auth;
use Illuminate\Http\Request;
use Input;
use Redirect;
use Session;
use URL;

class Settings extends AdmController
{
    /*
     * Account Security
     */

    public function postSecurityEnable(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $mshipAccount->current_security;

        if ($currentSecurity && $currentSecurity->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('You cannot enable security on this account.');
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get('securityLevel', 0));

        if (!$security) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('Invalid security ID specified.');
        }

        // Create them a blank security entry!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $mshipAccount->security()
            ->save($newSecurity);
        $security->accountSecurity()
            ->save($newSecurity);

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
            ->withSuccess('Security enabled for this account.');
    }

    public function postSecurityReset(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Let's check the user doesn't currently have security on their account.
        // We can't reset non-existant security!
        $currentSecurity = $mshipAccount->current_security;

        if (!$currentSecurity or !$currentSecurity->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('You cannot reset non-existant security.');
        }

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
            ->withSuccess('Security reset requested - user will receive an email.');
    }

    public function postSecurityChange(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Check the selected security ID exists!
        $security = SecurityData::find(Input::get('securityLevel', 0));

        if (!$security) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('Invalid security ID specified.');
        }

        // Let's check the user doesn't currently have security on their account.
        // We don't want to just override it for no reason, as that's bad.
        $currentSecurity = $mshipAccount->current_security;

        // It's also pointless changing to the same security ID.
        if (!$currentSecurity or !$currentSecurity->exists or $currentSecurity->security_id == $security->security_id) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
                ->withError('You cannot change security on this account.');
        }

        // Let's expire the current security
        $currentSecurity->expire();
        $currentSecurity->delete();

        // Now, let's make a new one!
        $newSecurity = new AccountSecurityData();
        $newSecurity->save();
        $mshipAccount->security()
            ->save($newSecurity);
        $security->accountSecurity()
            ->save($newSecurity);

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'security'])
            ->withSuccess('Security has been upgraded on this account.');
    }


    /*
     * Notes
     */

    public function postNoteCreate(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Is there any content?
        if (strlen(Input::get('content')) < 10) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
                ->withError('You cannot add such a short note!');
        }

        // Check this type exists!
        $noteType = NoteTypeData::find(Input::get('note_type_id'));
        if (!$noteType or !$noteType->exists) {
            return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
                ->withError('You selected an invalid note type.');
        }

        // Let's make a note and attach it to the user!
        $mshipAccount->addNote($noteType, Input::get('content'), Auth::user());

        return Redirect::route('adm.mship.account.details', [$mshipAccount->id, 'notes'])
            ->withSuccess('The note has been saved successfully!');
    }

    public function postNoteFilter(AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index');
        }

        // Get all filters
        $filters = Input::get('filter', []);
        $qs = '';
        foreach ($filters as $f) {
            $qs .= 'filter[' . $f . ']=1&';
        }

        return Redirect::to(URL::route('adm.mship.account.details', [$mshipAccount->id, 'notes']) . '?' . $qs);
    }


    /*
     * Troubleshooting
     */

    public function postImpersonate(Request $request, AccountData $mshipAccount)
    {
        if (!$mshipAccount) {
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
        if (!$mshipAccount) {
            return Redirect::route('adm.mship.account.index')
                ->withError('This user does not exist');
        }

        (new SyncSubscriber())->syncToAllServices(new AccountAltered($mshipAccount));

        return Redirect::back()
            ->withSuccess('User queued to sync to external services!');
    }
}
