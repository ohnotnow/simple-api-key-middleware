<?php

namespace Ohffs\SimpleApiKeyMiddleware;

use Closure;
use Ohffs\SimpleApiKeyMiddleware\ApiKey;
use Illuminate\Http\Response;

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

        if (! ApiKey::checkValidToken($request->bearerToken())) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
