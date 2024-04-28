<?php

namespace App\Http\Controllers;

use App\Repository\CriticRepositoryInterface;
use App\Repository\FilmRepositoryInterface;

class FilmCriticController extends Controller
{
    private FilmRepositoryInterface $filmRepository;
    private CriticRepositoryInterface $criticRepository;

    public function __construct(FilmRepositoryInterface $filmRepository, CriticRepositoryInterface $criticRepository)
    {
        $this->filmRepository = $filmRepository;
        $this->criticRepository = $criticRepository;
    }
}
