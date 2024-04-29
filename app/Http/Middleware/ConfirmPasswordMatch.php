<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConfirmPasswordMatch
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si le mot de passe confirmé correspond au nouveau mot de passe
        if (!Hash::check($request->password_confirmation, $request->new_password)) {
            abort(FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
