<?php

use Opcodes\LogViewer\Http\Middleware\AuthorizeLogViewer;

return [

    /*
    |--------------------------------------------------------------------------
    | Log Viewer
    |--------------------------------------------------------------------------
    | Log Viewer can be disabled, so it's no longer accessible via browser.
    |
    */

    'enabled' => env('LOG_VIEWER_ENABLED', true),

    'api_only' => env('LOG_VIEWER_API_ONLY', false),

    'require_auth_in_production' => true,

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Domain
    |--------------------------------------------------------------------------
    | You may change the domain where Log Viewer should be active.
    | If the domain is empty, all domains will be valid.
    |
    */

    'route_domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Route
    |--------------------------------------------------------------------------
    | Log Viewer will be available under this URL.
    |
    */

    'route_path' => 'log-viewer',

    /*
    |--------------------------------------------------------------------------
    | Back to system URL
    |--------------------------------------------------------------------------
    | When set, displays a link to easily get back to this URL.
    |
    */

    'back_to_system_url' => '/admin',

    'back_to_system_label' => 'Back to Admin',

    /*
    |--------------------------------------------------------------------------
    | Log Viewer route middleware.
    |--------------------------------------------------------------------------
    | Optional middleware to use when loading the initial Log Viewer page.
    |
    */

    'middleware' => [
        'web',
        AuthorizeLogViewer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Viewer API middleware.
    |--------------------------------------------------------------------------
    | Optional middleware to use on every API request. The same API is also
    | used from within the Log Viewer user interface.
    |
    | Uses the 'web' group to ensure sessions are started properly
    | for all API requests, avoiding 403 errors that can occur with
    | EnsureFrontendRequestsAreStateful in production environments.
    |
    */

    'api_middleware' => [
        'web',
        AuthorizeLogViewer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Viewer Remote hosts.
    |--------------------------------------------------------------------------
    */

    'hosts' => [
        'local' => [
            'name' => ucfirst(env('APP_ENV', 'local')),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Include file patterns
    |--------------------------------------------------------------------------
    */

    'include_files' => [
        '*.log',
        '**/*.log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude file patterns.
    |--------------------------------------------------------------------------
    */

    'exclude_files' => [
        '#\.php$#',      // Exclude PHP files
        '#\.json$#',     // Exclude JSON files
    ],

    /*
    |--------------------------------------------------------------------------
    | Shorter stack trace data.
    |--------------------------------------------------------------------------
    | When enabled, only the first frame of stack trace data is shown.
    |
    */

    'shorter_stack_trace_data' => env('LOG_VIEWER_SHORTER_STACK_TRACE_DATA', false),

    /*
    |--------------------------------------------------------------------------
    | Log Viewer theme
    |--------------------------------------------------------------------------
    | Available options: 'light', 'dark', or 'auto' (system default).
    |
    */

    'theme' => env('LOG_VIEWER_THEME', 'auto'),

];
