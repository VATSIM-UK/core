<?php

namespace App\Exceptions;

use App;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Vluzrmos\SlackApi\Facades\SlackChat;

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
     * @param  \Exception  $e
     * @return void
     * @throws \Exception $exception
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
     * @param  \Exception $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        return parent::render($request, $e);
    }

    protected function reportSlackError(Exception $e)
    {
        $channel = '#ws_alerts';

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
            SlackChat::message($channel, '', ['attachments' => $attachment, 'username' => 'Error Handling']);
        } catch (Exception $e) {
        }
    }
}
