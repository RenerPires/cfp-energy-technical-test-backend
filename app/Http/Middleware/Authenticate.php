<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\Factory as Auth;

class Authenticate
{
    protected Auth $auth;
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }
    public function handle($request, Closure $next, $guard = null): JsonResponse
    {
        if($token = $request->cookie('auth_token')){
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }

        if ($this->auth->guard($guard)->guest()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
