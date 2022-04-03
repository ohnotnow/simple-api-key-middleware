<?php

namespace Ohffs\SimpleApiKeyMiddleware;

use Closure;
use Illuminate\Http\Response;
use Ohffs\SimpleApiKeyMiddleware\SimpleApiKey;

class ApiKeyMiddleware
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
        if (! $request->bearerToken()) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if (! SimpleApiKey::checkValidToken($request->bearerToken())) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
