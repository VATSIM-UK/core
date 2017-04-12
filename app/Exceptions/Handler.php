<?php

namespace App\Exceptions;

use App;
use Log;
use Auth;
use Slack;
use Request;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
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
    ];

    /**
     * Report or log an exception.
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $e
     * @return void
     */
    public function report(Exception $e)
    {
        if (!$this->shouldntReport($e)) {
            if (extension_loaded('newrelic')) {
                try {
                    newrelic_notice_error(null, $e);
                } catch (Exception $e) {
                }
            }

            if (class_exists(App::class) && App::isBooted()) {
                $this->reportSlackError($e);
            }

            if (class_exists('Log')) {
                Log::info(Request::fullUrl());
            }
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }

    protected function reportSlackError(Exception $e)
    {
        if (App::environment('production')) {
            $channel = 'wslogging';
        } else {
            $channel = 'wslogging_dev';
        }

        $attachment = [
            'fallback' => 'Exception thrown: '.get_class($e),
            'text' => $e->getTraceAsString(),
            'author_name' => get_class($e),
            'color' => 'danger',
            'fields' => [
                [
                    'title' => 'Exception:',
                    'value' => (new \ReflectionClass($e))->getShortName(),
                    'short' => true,
                ],
                [
                    'title' => 'Message:',
                    'value' => $e->getMessage(),
                    'short' => true,
                ],
                [
                    'title' => 'File:',
                    'value' => $e->getFile(),
                    'short' => true,
                ],
                [
                    'title' => 'Line:',
                    'value' => $e->getLine(),
                    'short' => true,
                ],
                [
                    'title' => 'Code:',
                    'value' => $e->getCode(),
                    'short' => true,
                ],
            ],
        ];

        if (!App::runningInConsole()) {
            if (method_exists('Auth', 'check') && Auth::check()) {
                $attachment['fields'][] = [
                    'title' => 'Member:',
                    'value' => sprintf(
                        '%d - %s %s',
                        Auth::user()->id,
                        Auth::user()->name_first,
                        Auth::user()->name_last
                    ),
                    'short' => true,
                ];
            }

            $attachment['fields'][] = [
                'title' => 'Request path:',
                'value' => Request::url(),
                'short' => true,
            ];
        }

        try {
            Slack::setUsername('Error Handling')->to($channel)->attach($attachment)->send();
        } catch (Exception $e) {
        }
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('/');
    }
}
