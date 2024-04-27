<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserOwnership
{
    public function handle(Request $request, Closure $next)
    {
        // Récupérer l'utilisateur actuellement authentifié
        $user = $request->user();
        
        // Vérifier si l'utilisateur est présent et possède l'id concernée
        if ($user && $request->route('id') && $user->id == $request->route('id')) {
            return $next($request);
        }

        // Sinon, retourner une réponse d'erreur
        abort(FORBIDDEN, 'Forbidden');
    }
}
