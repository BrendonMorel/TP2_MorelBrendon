<?php

namespace App\Http\Controllers;

use App\Repository\CriticRepositoryInterface;

class CriticController extends Controller
{
    private CriticRepositoryInterface $criticRepository;

    public function __construct(CriticRepositoryInterface $criticRepository)
    {
        $this->criticRepository = $criticRepository;
    }
}
