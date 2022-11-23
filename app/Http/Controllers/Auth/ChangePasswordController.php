<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Auth;
use Illuminate\Http\Request;

/**
 * This controller is responsible for handling password creation,
 * modification and deletion requests for authenticated users.
 */
class ChangePasswordController extends BaseController
{
    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/mship/manage/dashboard';

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

    protected function validateOld(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|string|current_password',
        ]);
    }

    protected function validateNew(Request $request)
    {
        $this->validate($request, [
            'new_password' => 'required|string|confirmed|min:8|upperchars:1|lowerchars:1|numbers:1',
        ]);
    }

    protected function validateBoth(Request $request)
    {
        $this->validateOld($request);
        $this->validateNew($request);

        $this->validate($request, [
            'new_password' => 'different:old_password',
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
        $this->validateNew($request);

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
        $this->validateBoth($request);

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
        $this->validateOld($request);

        $this->account->removePassword();

        return redirect($this->redirectPath())->withSuccess('Password deleted successfully.');
    }
}
