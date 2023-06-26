<?php

namespace App\Traits\Middleware;

use Closure;
use Session;

trait RedirectsOnFailure
{
    use ExcludesRoutes;

    protected $sessionPrefix = 'middleware.failed';

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $sessionKey = $this->sessionPrefix;
        $class = get_class($this);

        // if other middleware is already failing, defer checking
        if (! empty(Session::get($this->sessionPrefix)) && ! in_array($class, Session::get($sessionKey))) {
            return $next($request);
        }

        if (! empty(Session::get($this->sessionPrefix)) && in_array($class, Session::get($sessionKey)) && $this->inExceptArray($request)) {
            // check if the middleware passes after the request is processed
            $response = $next($request);
            if (! $this->validate(false)) {
                return $this->pass($response);
            } else {
                return $response;
            }
        } elseif ($this->inExceptArray($request)) {
            return $next($request);
        }

        $status = $this->validate(true);
        if ($status) {
            return $this->fail($status);
        }

        return $this->pass($next($request));
    }

    protected function pass($response)
    {
        $sessionKey = $this->sessionPrefix;
        $class = get_class($this);

        if (Session::has($sessionKey) && in_array($class, Session::get($sessionKey))) {
            // remove the class from the array of failing middleware
            $failingMiddleware = array_diff(Session::pull($sessionKey), [$class]);
            if (! empty($failingMiddleware)) {
                Session::put($sessionKey, $failingMiddleware);
            }
        }

        return $response;
    }

    protected function fail($response)
    {
        $sessionKey = $this->sessionPrefix;
        $class = get_class($this);

        if (! Session::has($sessionKey) || ! in_array($class, Session::get($sessionKey))) {
            Session::push($sessionKey, $class);
        }

        return $response;
    }
}
