<?php

namespace App\Http\Middleware;

use Closure;

class AddTokenToHeader
{
  /**
   * Get the path the user should be redirected to when they are not authenticated.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return string|null
   */
  public function handle($request, Closure $next)
  {
    $authCookieName = config('jwt.auth_cookie_name');

    if (!$request->bearerToken()) {
      if ($request->hasCookie($authCookieName)) {
        $token = $request->cookie($authCookieName);
        $request->headers->add([
          'Authorization' => 'Bearer ' . $token
        ]);
      }
    }

    return $next($request);
  }
}
