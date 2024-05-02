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
            $critic = $this->criticRepository->getById($id);

            return (new CriticResource($critic))->response()->setStatusCode(OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function index()
    {
        try {
            $critics = $this->criticRepository->getAll();

            return CriticResource::collection($critics)->response()->setStatusCode(OK);
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
