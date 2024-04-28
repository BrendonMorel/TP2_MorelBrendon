<?php

namespace App\Http\Controllers;

use App\Repository\ActorRepositoryInterface;
use App\Repository\FilmRepositoryInterface;

class ActorFilmController extends Controller
{
    private ActorRepositoryInterface $actorRepository;
    private FilmRepositoryInterface $filmRepository;

    public function __construct(ActorRepositoryInterface $actorRepository, FilmRepositoryInterface $filmRepository)
    {
        $this->actorRepository = $actorRepository;
        $this->filmRepository = $filmRepository;
    }
}
