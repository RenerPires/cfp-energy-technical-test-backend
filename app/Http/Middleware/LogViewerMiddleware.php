<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LogViewerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
//        abort_unless(
//            $request->user()?->tokenCan('log-viewer:view'),
//            403,
//            'Unauthorized'
//        );

        return $next($request);
    }
}
