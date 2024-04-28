<?php

namespace App\Http\Controllers;

use App\Http\Requests\ActorRequest;
use App\Http\Resources\ActorResource;
use App\Repository\ActorRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ActorController extends Controller
{
    private ActorRepositoryInterface $actorRepository;

    public function __construct(ActorRepositoryInterface $actorRepository)
    {
        $this->actorRepository = $actorRepository;
    }

    public function store(ActorRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $this->actorRepository->create($validatedData);

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
            $this->actorRepository->delete($id);

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
            $actor = $this->actorRepository->getById($id);

            return (new ActorResource($actor))->response()->setStatusCode(OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function index()
    {
        try {
            $actors = $this->actorRepository->getAll();

            return ActorResource::collection($actors)->response()->setStatusCode(OK);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function update(ActorRequest $request, int $id)
    {
        try {
            $validatedData = $request->validated();

            $this->actorRepository->update($id, $validatedData);

            return response()->json(['message' => UPDATED_MSG], OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }
}
