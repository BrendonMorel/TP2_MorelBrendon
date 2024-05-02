<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilmRequest;
use App\Http\Resources\FilmResource;
use App\Repository\FilmRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class FilmController extends Controller
{
    private FilmRepositoryInterface $filmRepository;

    public function __construct(FilmRepositoryInterface $filmRepository)
    {
        $this->filmRepository = $filmRepository;
    }

    /**
     * @OA\Post(
     *     path="/api/films",
     *     tags={"Films"},
     *     summary="Crée un nouveau film",
     *     description="Crée un nouveau film dans la base de données avec les données fournies.",
     *     security={{"Token": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du nouveau film",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"title", "release_year", "length", "description", "rating", "language_id", "special_features", "image"},
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="Titre du film"
     *                 ),
     *                 @OA\Property(
     *                     property="release_year",
     *                     type="integer",
     *                     format="int32",
     *                     default=2000,
     *                     description="Année de sortie du film"
     *                 ),
     *                 @OA\Property(
     *                     property="length",
     *                     type="integer",
     *                     default=60,
     *                     description="Durée du film en minutes"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Description du film"
     *                 ),
     *                 @OA\Property(
     *                     property="rating",
     *                     type="string",
     *                     maxLength=4,
     *                     description="Classification du film"
     *                 ),
     *                 @OA\Property(
     *                     property="language_id",
     *                     type="integer",
     *                     default=1,
     *                     description="ID de la langue du film"
     *                 ),
     *                 @OA\Property(
     *                     property="special_features",
     *                     type="string",
     *                     description="Caractéristiques spéciales du film"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     description="URL de l'image du film"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Film créé avec succès",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé - Utilisateur non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Entité non traitable - Erreur de validation"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur"
     *     )
     * )
     */
    public function store(FilmRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $this->filmRepository->create($validatedData);

            return response()->json(['message' => CREATED_MSG], CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => INVALID_DATA_MSG], INVALID_DATA);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/films/{id}",
     *     tags={"Films"},
     *     summary="Supprime un film par son ID",
     *     description="Supprime un film de la base de données en fonction de son ID.",
     *     security={{"Token": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du film à supprimer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Film supprimé avec succès - Aucun contenu retourné",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé - Utilisateur non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Film non trouvé - L'ID du film n'existe pas"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        try {
            $this->filmRepository->delete($id);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function show(int $id)
    {
        try {
            $film = $this->filmRepository->getById($id);

            return (new FilmResource($film))->response()->setStatusCode(OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function index()
    {
        try {
            $films = $this->filmRepository->getAll();

            return FilmResource::collection($films)->response()->setStatusCode(OK);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/films/{id}",
     *     tags={"Films"},
     *     summary="Met à jour un film par son ID",
     *     description="Met à jour un film dans la base de données en fonction de son ID.",
     *     security={{"Token": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID du film à mettre à jour",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données du film à mettre à jour",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"title", "release_year", "length", "description", "rating", "language_id", "special_features", "image"},
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     description="Titre du film"
     *                 ),
     *                 @OA\Property(
     *                     property="release_year",
     *                     type="integer",
     *                     default=1999,
     *                     description="Année de sortie du film"
     *                 ),
     *                 @OA\Property(
     *                     property="length",
     *                     type="integer",
     *                     default=100,
     *                     description="Durée du film en minutes"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     description="Description du film"
     *                 ),
     *                 @OA\Property(
     *                     property="rating",
     *                     type="string",
     *                     maxLength=4,
     *                     description="Classification du film"
     *                 ),
     *                 @OA\Property(
     *                     property="language_id",
     *                     type="integer",
     *                     default=2,
     *                     description="ID de la langue du film"
     *                 ),
     *                 @OA\Property(
     *                     property="special_features",
     *                     type="string",
     *                     description="Caractéristiques spéciales du film"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     description="URL de l'image du film"
     *                 ),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Film mis à jour avec succès",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé - Utilisateur non authentifié"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Film non trouvé - L'ID du film n'existe pas"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Entité non traitable - Erreur de validation"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur"
     *     )
     * )
     */
    public function update(FilmRequest $request, int $id)
    {
        try {
            $validatedData = $request->validated();

            $this->filmRepository->update($id, $validatedData);

            return response()->json(['message' => UPDATED_MSG], OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }
}
