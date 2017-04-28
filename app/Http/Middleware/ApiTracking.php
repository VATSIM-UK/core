<?php

namespace App\Http\Middleware;

use Closure;

class ApiTracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $apiRequest = \App\Models\Api\Request::create([
            'api_account_id' => \Auth::guard('api')->user()->id,
            'method' => $request->method(),
            'url_name' => $request->route()->getName(),
            'url_full' => $request->url(),
        ]);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $apiRequest = \App\Models\Api\Request::where('api_account_id', '=', \Auth::guard('api')->user()->id)
                                             ->where('method', '=', $request->method())
                                             ->whereNull('response_code')
                                             ->orderBy('created_at', 'DESC')->first();

        $apiRequest->response_code = $response->status();
        $apiRequest->response_full = $response->content();
        $apiRequest->save();
    }
}
