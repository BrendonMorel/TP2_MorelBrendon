<?php

namespace App\Http\Middleware;

use App\Models\Critic;
use App\Models\Film;
use Closure;
use Illuminate\Http\Request;

class CheckIsFilmDeletable
{
    public function handle(Request $request, Closure $next)
    {
        $film_id = $request->route('id');

        // Vérifier s'il existe une critique pour ce film
        $existingCritic = Critic::where('film_id', $film_id)->exists();

        // Vérifier s'il existe une relation pour ce film et un acteur
        $film = Film::find($film_id);
        if (!$film) {
            return $next($request);
        }
        
        $existingActorRelation = $film->actors()->exists();
        if ($existingCritic || $existingActorRelation) {
            abort(FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
