<?php

namespace App\Providers;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Ban;
use App\Models\Mship\Account\EndorsementRequest;
use App\Models\Mship\Account\Note;
use App\Models\Mship\Feedback\Feedback;
use App\Models\Smartcars;
use App\Models\Training\WaitingList;
use App\Models\VisitTransfer;
use App\Policies\FeedbackPolicy;
use App\Policies\Mship\Account\BanPolicy;
use App\Policies\Mship\Account\EndorsementRequestPolicy;
use App\Policies\Mship\Account\NotePolicy;
use App\Policies\PasswordPolicy;
use App\Policies\PositionGroupPolicy;
use App\Policies\RolePolicy;
use App\Policies\Smartcars\ExercisePolicy;
use App\Policies\Smartcars\PirepPolicy;
use App\Policies\Training\WaitingListFlagsPolicy;
use App\Policies\Training\WaitingListPolicy;
use App\Policies\VisitTransfer\ApplicationPolicy;
use App\Policies\VisitTransfer\ReferencePolicy;
use App\Registrars\PermissionRegistrar as RegistrarsPermissionRegistrar;
use Illuminate\Contracts\Auth\Access\Gate as AccessGate;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        EndorsementRequest::class => EndorsementRequestPolicy::class,
        PositionGroup::class => PositionGroupPolicy::class,

        Ban::class => BanPolicy::class,
        Role::class => RolePolicy::class,
        Note::class => NotePolicy::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Custom spatie permissions override
        $this->app->singleton(PermissionRegistrar::class, RegistrarsPermissionRegistrar::class);

        $this->callAfterResolving(AccessGate::class, function (AccessGate $gate, Application $app) {
            /** @var PermissionRegistrar $permissionLoader */
            $permissionLoader = $app->get(PermissionRegistrar::class);
            $permissionLoader->clearPermissionsCollection();
            $permissionLoader->registerPermissions($gate);
        });

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
