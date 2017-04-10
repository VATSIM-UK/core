<?php

namespace App\Providers;

use Gate;
use App\Models\Mship\Account;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->serviceAccessGates();
    }

    /**
     * Define the gates to authorise access to different services.
     */
    protected function serviceAccessGates()
    {
        Gate::define('register-slack', function (Account $user) {
            $correctState = $user->hasState('division') || $user->hasState('visiting') || $user->hasState('transferring');
            $isRegistered = !is_null($user->slack_id);

            return $correctState && !$isRegistered;
        });
    }
}
