<?php

namespace App\Traits\Middleware;

use Illuminate\Http\Request;

trait ExcludesRoutes
{
    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function inExceptArray(Request $request)
    {
        if (isset($this->except)) {
            foreach ($this->except as $except) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                if ($request->is($except)) {
                    return true;
                }
            }
        }

        return false;
    }
}
