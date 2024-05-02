<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriticRequest;
use App\Models\User;
use App\Repository\CriticRepositoryInterface;
use App\Repository\FilmRepositoryInterface;
use Exception;
use Illuminate\Validation\ValidationException;

class FilmCriticController extends Controller
{
    private FilmRepositoryInterface $filmRepository;
    private CriticRepositoryInterface $criticRepository;

    public function __construct(FilmRepositoryInterface $filmRepository, CriticRepositoryInterface $criticRepository)
    {
        $this->filmRepository = $filmRepository;
        $this->criticRepository = $criticRepository;
    }

        /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CriticRequest  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path="/api/films/{film_id}/critics",
     *     summary="Enregistrer une nouvelle critique",
     *     tags={"Critics"},
     *     security={{"Token": {}}},
     *     @OA\Parameter(
     *         name="filmId",
     *         in="path",
     *         required=true,
     *         description="ID du film",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"score", "comment"},
     *             @OA\Property(property="score", type="number", format="float", example=8.5),
     *             @OA\Property(property="comment", type="string", example="Great movie!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Critique crée avec succès",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Interdit - Vous n'avez pas les permissions"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Entité non traitable - Erreur de validation"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur"
     *     ),
     * )
     */
    public function store(CriticRequest $request, int $filmId)
    {
        try {
            // Récupérer les données validées de la demande
            $validatedData = $request->validated();
            $userId = $request->user()->id;

            // Vérifier si l'utilisateur et le film existent
            if (!User::find($userId) || !$this->filmRepository->getById($filmId)) {
                return response()->json(['error' => INVALID_DATA_MSG], INVALID_DATA);
            }

            $validatedData['user_id'] = $userId;
            $validatedData['film_id'] = $filmId;

            // Créer la critique en utilisant les données validées
            $this->criticRepository->create($validatedData);

            return response()->json(['message' => CREATED_MSG], CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => INVALID_DATA_MSG], INVALID_DATA);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }
}
