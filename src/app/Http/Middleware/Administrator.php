<?php

namespace App\Http\Middleware;

use Closure;

class Administrator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->isStaff() || auth()->user()->isExecutive()) {
            return responseError(403, __('message.unauthorized'));
        }
        return $next($request);
    }
}
