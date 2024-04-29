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

        $film = Film::find($film_id);
        if (!$film) {
            return $next($request);
        }
        
        // Vérifier s'il existe une critique pour ce film
        $existingCriticRelation = $film->critics()->exists();

        // Vérifier s'il existe une relation pour ce film et un acteur
        $existingActorRelation = $film->actors()->exists();
        
        if ($existingCriticRelation || $existingActorRelation) {
            abort(FORBIDDEN, 'Forbidden');
        }

        return $next($request);
    }
}
