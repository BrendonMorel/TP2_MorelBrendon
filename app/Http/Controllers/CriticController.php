<?php

namespace App\Http\Controllers;

use App\Http\Requests\CriticRequest;
use App\Http\Resources\CriticResource;
use App\Models\Film;
use App\Models\User;
use App\Repository\CriticRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class CriticController extends Controller
{
    private CriticRepositoryInterface $criticRepository;

    public function __construct(CriticRepositoryInterface $criticRepository)
    {
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
     *         name="film_id",
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
     *         description="Created",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid data"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     ),
     * )
     */
    public function store(CriticRequest $request, int $film_id)
    {
        try {
            // Récupérer les données validées de la demande
            $validatedData = $request->validated();
            $user_id = $request->user()->id;

            // Vérifier si l'utilisateur et le film existent
            if (!User::find($user_id) || !Film::find($film_id)) {
                return response()->json(['error' => INVALID_DATA_MSG], INVALID_DATA);
            }

            $validatedData['user_id'] = $user_id;
            $validatedData['film_id'] = $film_id;

            // Créer la critique en utilisant les données validées
            $this->criticRepository->create($validatedData);

            return response()->json(['message' => CREATED_MSG], CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => INVALID_DATA_MSG], INVALID_DATA);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->criticRepository->delete($id);

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
            $film = $this->criticRepository->getById($id);

            return (new CriticResource($film))->response()->setStatusCode(OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function index()
    {
        try {
            $films = $this->criticRepository->getAll();

            return CriticResource::collection($films)->response()->setStatusCode(OK);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function update(CriticRequest $request, int $id)
    {
        try {
            $validatedData = $request->validated();

            $this->criticRepository->update($id, $validatedData);

            return response()->json(['message' => UPDATED_MSG], OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }
}
