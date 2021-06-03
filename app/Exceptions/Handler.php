<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Symfony\Component\Console\Exception\CommandNotFoundException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
        \Illuminate\Queue\MaxAttemptsExceededException::class,
        \League\OAuth2\Server\Exception\OAuthServerException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
        'old_password',
        'new_password',
        'new_password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  Throwable  $e
     * @return void
     * @throws Throwable $exception
     */
    public function report(Throwable $e)
    {
        if (! $this->shouldntReport($e)) {
            if (class_exists('Log')) {
                Log::info(Request::fullUrl());
            }
        }

        if (app()->bound('sentry') && $this->shouldReport($e)) {
            \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                $scope->setUser([
                    'id' => auth()->user() ? auth()->user()->id : 'Guest',
                    'username' => auth()->user() ? auth()->user()->name : 'Guest',
                    'email' => auth()->user() ? auth()->user()->email : 'Guest',
                ]);
            });
            app('sentry')->captureException($e);
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Throwable $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Throwable $e)
    {
        return parent::render($request, $e);
    }
}
