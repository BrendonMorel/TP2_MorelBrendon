<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Vérifiez si l'utilisateur est connecté et a le rôle requis
        if (! $request->user() || ! $request->user()->role || $request->user()->role->name !== $role) {
            return response()->json(['error' => FORBIDDEN_MSG], FORBIDDEN);
        }

        return $next($request);
    }
}
