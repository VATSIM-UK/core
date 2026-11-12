<?php

namespace App\Providers;

use App\Http\Responses\Auth\TwoFactorConfirmedResponse;
use App\Http\Responses\Auth\TwoFactorLoginResponse;
use App\Listeners\Mship\LockTwoFactorChallenge;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Fortify::ignoreRoutes();

        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(TwoFactorConfirmedResponseContract::class, TwoFactorConfirmedResponse::class);
    }

    public function boot(): void
    {
        Fortify::twoFactorChallengeView('auth.two-factor.challenge');

        RateLimiter::for('two-factor', function (Request $request) {
            $key = $request->session()->get('login.id') ?? $request->ip();

            if (RateLimiter::attempts(LockTwoFactorChallenge::lockKey($key)) > 0) {
                return redirect()->route('two-factor.login')->withErrors([
                    'code' => 'Too many failed attempts. Please try again in '.LockTwoFactorChallenge::LOCKOUT_MINUTES.' minutes.',
                ]);
            }

            return Limit::perMinute(5)->by($key);
        });
    }
}
