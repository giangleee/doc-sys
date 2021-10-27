<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!empty($request->user())) {
            if (!$request->user()->status) {
                return responseError(401, __('message.status_disable'));
            }
        } else {
            if ($request->has('employee_id')) {
                $userRepository = new UserRepository();
                $user = $userRepository->findByEmployeeId($request->employee_id);
                if (!empty($user) && !$user->status) {
                    return responseError(401, __('message.status_disable'));
                }
            }
        }

        return $next($request);
    }
}
