<?php

namespace App\Http\Middleware;

use App\Repositories\DocumentRepository;
use Closure;

class CheckPermissionDocument
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
        $documentRepository = new DocumentRepository();
        if (!$documentRepository->checkPermissionDocument($request->route('id'), $request->is_update)) {
            return responseError(403, __('message.unauthorized'));
        }
        return $next($request);
    }
}
