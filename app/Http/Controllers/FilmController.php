<?php

namespace App\Http\Controllers;

use App\Http\Requests\FilmRequest;
use App\Http\Resources\FilmResource;
use App\Repository\FilmRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FilmController extends Controller
{
    private FilmRepositoryInterface $filmRepository;

    public function __construct(FilmRepositoryInterface $filmRepository)
    {
        $this->filmRepository = $filmRepository;
    }

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
