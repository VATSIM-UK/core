<?php

namespace App\Providers;

use App\Models\Community\Membership;
use App\Models\Mship\Account;
use App\Models\VisitTransfer\Application;
use App\Models\VisitTransfer\Reference;
use App\Policies\MembershipPolicy;
use App\Policies\PasswordPolicy;
use App\Policies\VisitTransfer\ApplicationPolicy;
use App\Policies\VisitTransfer\ReferencePolicy;
use Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'password' => PasswordPolicy::class,
        Membership::class => MembershipPolicy::class,
        Application::class => ApplicationPolicy::class,
        Reference::class => ReferencePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        Passport::routes(function ($router) {
            $router->forAuthorization();
            $router->forAccessTokens();
            //$router->forTransientTokens(); // the tokens we issue are permanent
            //$router->forClients(); // we don't want external applications using our oauth flows
            //$router->forPersonalAccessTokens(); // we don't have a user-facing API yet
        });

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
