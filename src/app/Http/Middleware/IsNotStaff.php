<?php

namespace App\Http\Middleware;

use Closure;

class IsNotStaff
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
        if (auth()->user()->isStaff()) {
            return responseError(403, __('message.unauthorized'));
        }
        return $next($request);
    }
}
