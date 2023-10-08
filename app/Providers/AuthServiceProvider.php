<?php

namespace App\Providers;

use App\Models\Mship\Account\Ban;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Smartcars;
use App\Models\Training\WaitingList;
use App\Models\VisitTransfer;
use App\Policies\FeedbackPolicy;
use App\Policies\Mship\Account\BanPolicy;
use App\Policies\PasswordPolicy;
use App\Policies\RolePolicy;
use App\Policies\Smartcars\ExercisePolicy;
use App\Policies\Smartcars\PirepPolicy;
use App\Policies\Training\WaitingListFlagsPolicy;
use App\Policies\Training\WaitingListPolicy;
use App\Policies\VisitTransfer\ApplicationPolicy;
use App\Policies\VisitTransfer\ReferencePolicy;
use App\Registrars\PermissionRegistrar as RegistrarsPermissionRegistrar;
use Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'password' => PasswordPolicy::class,
        Smartcars\Flight::class => ExercisePolicy::class,
        Smartcars\Pirep::class => PirepPolicy::class,
        VisitTransfer\Application::class => ApplicationPolicy::class,
        VisitTransfer\Reference::class => ReferencePolicy::class,
        WaitingList::class => WaitingListPolicy::class,
        WaitingList\WaitingListFlag::class => WaitingListFlagsPolicy::class,
        Qualification::class => QualificationPolicy::class,
        Feedback::class => FeedbackPolicy::class,

        Ban::class => BanPolicy::class,
        Role::class => RolePolicy::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Custom spatie permissions override
        $this->app->singleton(PermissionRegistrar::class, RegistrarsPermissionRegistrar::class);
    }

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // TODO: Remove use-permission
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
    }
}
