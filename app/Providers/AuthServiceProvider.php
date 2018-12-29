<?php

namespace App\Providers;

use App\Models\Community;
use App\Models\Smartcars;
use App\Models\VisitTransfer;
use App\Policies\GroupPolicy;
use App\Policies\MembershipPolicy;
use App\Policies\PasswordPolicy;
use App\Policies\Smartcars\ExercisePolicy;
use App\Policies\Smartcars\PirepPolicy;
use App\Policies\VisitTransfer\ApplicationPolicy;
use App\Policies\VisitTransfer\ReferencePolicy;
use Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'password' => PasswordPolicy::class,
        Community\Membership::class => MembershipPolicy::class,
        Community\Group::class => GroupPolicy::class,
        Smartcars\Flight::class => ExercisePolicy::class,
        Smartcars\Pirep::class => PirepPolicy::class,
        VisitTransfer\Application::class => ApplicationPolicy::class,
        VisitTransfer\Reference::class => ReferencePolicy::class,
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

        Gate::define('use-permission', function ($user, $permission) {
            if ($user->hasRole('privacc') && config()->get('app.env') != 'production') {
                return true;
            }

            try {
                return auth()->user()->hasPermissionTo($permission);
            } catch (PermissionDoesNotExist $e) {
                return false;
            }
        });

        Gate::define('ukcp-beta', function ($user) {
            $betaTesters = collect([
                858680,
                1169992,
                1381570,
                1237658,
                1240481,
                1339920,
                1294298,
                1335936,
                1305268,
                1330403,
                1284743,
                856109,
                1002707,
                1138314,
                1317737,
                1258635,
                1285647
            ]);

            return $betaTesters->contains($user->id) || app()->isLocal();
        });

        $this->serviceAccessGates();
    }

    /**
     * Define the gates to authorise access to different services.
     */
    protected function serviceAccessGates()
    {
        //
    }
}
