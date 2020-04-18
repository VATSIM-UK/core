<?php

namespace App\Http\Controllers;

use App\Models\Mship\Account;
use App\Models\User;
use Vatsim\OAuth\SSO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

/**
 * Class LoginController.
 */
class SSOLoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * @var SSO
     */
    private $sso;

    /**
     * LoginController constructor.
     */
    public function __construct()
    {
        $this->sso = new SSO(config('sso.base'), config('sso.key'), config('sso.secret'), config('sso.method'), config('sso.cert'), config('sso.additionalConfig'));
    }

    /**
     * Redirect user to VATSIM SSO for login.
     *
     * @param Request $request
     * @throws \Vatsim\OAuth\SSOException
     */
    public function login(Request $request)
    {
        return $this->sso->login(config('sso.return'), function ($key, $secret, $url) use ($request) {
            $request->session()->put('vatsimauth', compact('key', 'secret'));

            return redirect($url);
        }, function ($error) {
            Log::error('SSO Login Error - ' . $error->getMessage());

            return redirect('/')->withError(['Login failed', $error->getMessage()]);
        });
    }

    /**
     * Validate the login and access protected resources.
     *
     * @param Request $get
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Vatsim\OAuth\SSOException
     */
    public function validateLogin(Request $request)
    {
        $session = $request->session()->get('vatsimauth');

        return $this->sso->validate(
            $session['key'],
            $session['secret'],
            $request->input('oauth_verifier'),
            function ($user) use ($request) {
                $request->session()->forget('vatsimauth');
                $account = $this->completeLogin($user);
                Auth::login($account, true);

                return redirect()->intended('/');
            },
            function ($error) use ($request) {
                Log::error('SSO Validation Error - ' . $error->getMessage());

                return redirect('/')->withError(['Login failed', $error->getMessage()]);
            }
        );
    }

    public function completeLogin($user)
    {
        $account = Account::firstOrNew(['id' => $user->id]);

        $account->name_first = $user->name_first;
        $account->name_last = $user->name_last;
        $account->email = $user->email;
        $account->save();

        return $account;
    }

    /**
     * Log the user out.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function logout()
    {
        Auth::logout();

        return redirect()->to('/');
    }
}
