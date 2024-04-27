<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $requestedUserId = $request->route('id');
        $authenticatedUserId = $request->user()->id;

        if ($requestedUserId != $authenticatedUserId) {
            abort(FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
