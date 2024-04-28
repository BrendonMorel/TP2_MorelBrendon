<?php

namespace App\Http\Controllers;

use App\Repository\LanguageRepositoryInterface;

class LanguageController extends Controller
{
    private LanguageRepositoryInterface $languageRepository;

    public function __construct(LanguageRepositoryInterface $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }
}
