<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Mship\DuplicatePasswordException;
use App\Http\Controllers\BaseController;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Input;
use Redirect;
use Session;

/**
 * This controller is responsible for handling password reset requests
 * and uses a simple trait to include this behavior. You're free to
 * explore this trait and override any methods you wish to tweak.
 * @package App\Http\Controllers\Auth
 */
class ChangePasswordController extends BaseController
{
    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth_full_group');
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'password' => 'required|confirmed|min:6',
        ];
    }

    /**
     * Get the password reset credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return array_merge(['id' => Session::get('auth.vatsim-sso')], $request->only(
            'password', 'password_confirmation', 'token'
        ));
    }

    public function showCreateForm()
    {
        if ($this->account->hasPassword()) {
            return redirect($this->redirectPath())->withError('You already have a password set.');
        }

        return $this->viewMake('auth.passwords.create');
    }

    public function create(Request $request)
    {
        if (Auth::user()->hasPassword()) {
            if (!Auth::user()->verifyPassword(Input::get('old_password'))) {
                return back()->with('error', 'Your old password is incorrect.  Please try again.');
            }
        }

        $this->validate($request, [
            'new_password' => 'required|confirmed|min:6',
        ]);

        $newPassword = Input::get('new_password');

        // Check the number of alphabetical characters.
        if (preg_match_all('/[a-zA-Z]/', $newPassword) < 3) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password must have at least 3 alphabetical characters)');
        }

        // Check the number of numeric characters.
        if (preg_match_all('/[0-9]/', $newPassword) < 1) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password must have at least one number)');
        }

        // All requirements met, set the password!
        try {
            Auth::user()->setPassword($newPassword);
        } catch (DuplicatePasswordException $e) {
            return Redirect::route('mship.security.replace')->with('error', 'Your new password cannot be the same as your old password.');
        }

        Session::put('auth.secondary', Carbon::now());
        $request->session()->put([
            'password_hash' => $request->user()->getAuthPassword(),
        ]);

        return redirect()->route('default')->withSuccess('Password set successfully.');
    }

    public function showChangeForm()
    {
        return $this->viewMake('auth.passwords.change');
    }

    public function change(Request $request)
    {
        if (Auth::user()->hasPassword()) {
            if (!Auth::user()->verifyPassword(Input::get('old_password'))) {
                return back()->with('error', 'Your old password is incorrect.  Please try again.');
            }
        }

        $this->validate($request, [
            'new_password' => 'required|confirmed|min:6',
        ]);

        $newPassword = Input::get('new_password');

        // Check the number of alphabetical characters.
        if (preg_match_all('/[a-zA-Z]/', $newPassword) < 3) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password must have at least 3 alphabetical characters)');
        }

        // Check the number of numeric characters.
        if (preg_match_all('/[0-9]/', $newPassword) < 1) {
            return Redirect::route('mship.security.replace')->with('error', 'Your password does not meet the requirements (password must have at least one number)');
        }

        // All requirements met, set the password!
        try {
            Auth::user()->setPassword($newPassword);
        } catch (DuplicatePasswordException $e) {
            return Redirect::route('mship.security.replace')->with('error', 'Your new password cannot be the same as your old password.');
        }

        Session::put('auth.secondary', Carbon::now());
        $request->session()->put([
            'password_hash' => $request->user()->getAuthPassword(),
        ]);

        return redirect()->route('default')->withSuccess('Password reset successfully.');
    }

    public function showDeleteForm()
    {
        if ($this->account->mandatory_password) {
            return redirect()->route('mship.manage.dashboard')->withError('You cannot disable your secondary password.');
        } elseif (!$this->account->hasPassword()) {
            return redirect()->route('mship.manage.dashboard')->withError('You do not have a secondary password to disable.');
        }

        return $this->viewMake('auth.passwords.delete');
    }

    public function delete(Request $request)
    {
        if ($this->account->mandatory_password) {
            return redirect()->route('mship.manage.dashboard')->withError('You cannot disable your secondary password.');
        } elseif (!$this->account->hasPassword()) {
            return redirect()->route('mship.manage.dashboard')->withError('You do not have a secondary password to disable.');
        } elseif (!$this->account->verifyPassword($request->input('old_password'))) {
            return back()->with('error', 'Your old password is incorrect.  Please try again.');
        }

        $this->account->removePassword();

        return redirect()->route('mship.manage.dashboard')->withSuccess('Your secondary password has been deleted successfully.');
    }
}
