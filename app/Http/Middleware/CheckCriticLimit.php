<?php

namespace App\Http\Middleware;

use App\Models\Critic;
use Closure;
use Illuminate\Http\Request;

class CheckCriticLimit
{
    public function handle(Request $request, Closure $next)
    {
        $film_id = $request->route('film_id');

        $user_id = $request->user()->id;

        // Vérifiez si l'utilisateur a déjà créé une critique pour ce film
        $existingCritic = Critic::where('user_id', $user_id)->where('film_id', $film_id)->exists();
        if ($existingCritic) {
            abort(FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
