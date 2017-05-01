<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\Mship\DuplicatePasswordException;
use App\Http\Controllers\BaseController;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Input;
use Redirect;
use Session;

/**
 * This controller is responsible for handling password creation,
 * modification and deletion requests for authenticated users.
 *
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

    protected function validateOldPassword(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|string|password',
        ]);
    }

    protected function validateNewPassword(Request $request)
    {
        $this->validate($request, [
            'new_password' => 'required|string|confirmed|min:8|upperchars:1|lowerchars:1|numbers:1|different:old_password',
        ]);
    }

    public function showCreateForm()
    {
        $this->authorize('create', 'password');

        return $this->viewMake('auth.passwords.create');
    }

    public function create(Request $request)
    {
        $this->authorize('create', 'password');
        $this->validateNewPassword($request);

        Auth::user()->setPassword($request->input('new_password'));

        return redirect($this->redirectPath())->withSuccess('Password set successfully.');
    }

    public function showChangeForm()
    {
        $this->authorize('change', 'password');

        return $this->viewMake('auth.passwords.change');
    }

    public function change(Request $request)
    {
        $this->authorize('change', 'password');
        $this->validateOldPassword($request);
        $this->validateNewPassword($request);

        Auth::user()->setPassword($request->input('new_password'));

        return redirect($this->redirectPath())->withSuccess('Password reset successfully.');
    }

    public function showDeleteForm()
    {
        $this->authorize('delete', 'password');

        return $this->viewMake('auth.passwords.delete');
    }

    public function delete(Request $request)
    {
        $this->authorize('delete', 'password');
        $this->validateOldPassword($request);

        $this->account->removePassword();

        return redirect($this->redirectPath())->withSuccess('Password deleted successfully.');
    }
}
