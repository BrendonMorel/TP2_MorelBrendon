<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ConfirmPasswordMatch
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si le mot de passe confirmé correspond au nouveau mot de passe
        if ($request->password_confirmation !== $request->new_password) {
            return response()->json(['error' => FORBIDDEN_MSG], FORBIDDEN);
        }

        return $next($request);
    }
}
