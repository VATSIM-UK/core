<?php

namespace App\Http;

use App\Http\Middleware\RecordLoginInfo;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Spatie\CookieConsent\CookieConsentMiddleware::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            // native
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

            // custom
            \App\Http\Middleware\TrackInactivity::class,
        ],
        'api' => [
            'throttle:60,1',
            'bindings',
        ],
        'api_auth' => [
            'auth:api',
            'api.tracking',
        ],
        'auth_full_group' => [
            'auth',
            'auth.record-info',
            'mandatorypasswords',
            'denyifbanned',
            'user.must.read.notifications',
            'redirecttointended',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        // native
        'auth'            => \App\Http\Middleware\Authenticate::class,
        'auth.basic'      => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings'        => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can'             => \Illuminate\Auth\Middleware\Authorize::class,
        'guest'           => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle'        => \Illuminate\Routing\Middleware\ThrottleRequests::class,

        // custom
        'admin'                        => Middleware\CheckAdminPermissions::class,
        'user.must.read.notifications' => Middleware\UserMustReadNotifications::class,
        'api.tracking'                 => \App\Http\Middleware\ApiTracking::class,
        'denyifbanned'                 => Middleware\DenyIfBanned::class,
        'mandatorypasswords'           => Middleware\MandatoryPasswords::class,
        'redirecttointended'           => Middleware\RedirectToIntended::class,
        'auth.record-info'             => RecordLoginInfo::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \App\Http\Middleware\TrackInactivity::class,
        \App\Http\Middleware\Authenticate::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];

    protected function bootstrappers()
    {
        return array_merge(
            [\Bugsnag\BugsnagLaravel\OomBootstrapper::class],
            parent::bootstrappers(),
        );
    }
}
