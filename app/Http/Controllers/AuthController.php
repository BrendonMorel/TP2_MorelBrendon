<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @OA\Post(
     *     path="/api/signup",
     *     tags={"Authentification"},
     *     summary="Crée un utilisateur",
     *     description="Crée un nouvel utilisateur avec les données fournies.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données utilisateur",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"login", "password", "email", "last_name", "first_name"},
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     description="Nom d'utilisateur de l'utilisateur"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     description="Mot de passe de l'utilisateur"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string",
     *                     format="email",
     *                     description="Adresse e-mail de l'utilisateur"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string",
     *                     description="Nom de famille de l'utilisateur"
     *                 ),
     *                 @OA\Property(
     *                     property="first_name",
     *                     type="string",
     *                     description="Prénom de l'utilisateur"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Utilisateur créé avec succès"
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
    public function register(Request $request)
    {
        try {
            $request->validate([
                'login' => 'required|max:50|unique:users',
                'password' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'last_name' => 'required|max:50',
                'first_name' => 'required|max:50'
            ]);

            $this->userRepository->create([
                'login' => $request['login'],
                'password' => bcrypt($request['password']),
                'email' => $request['email'],
                'last_name' => $request['last_name'],
                'first_name' => $request['first_name']
            ]);

            return response()->json(['message' => CREATED_MSG], CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => INVALID_DATA_MSG], BAD_REQUEST);
        } catch (Exception $exe) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/signin",
     *     tags={"Authentification"},
     *     summary="Connexion de l'utilisateur",
     *     description="Authentifie l'utilisateur avec les informations fournies et retourne un jeton d'authentification en cas de succès.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données d'identification de l'utilisateur",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"login", "password"},
     *                 @OA\Property(
     *                     property="login",
     *                     type="string",
     *                     maxLength=50,
     *                     description="Nom d'utilisateur de l'utilisateur"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Mot de passe de l'utilisateur"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Connexion réussie - Un jeton d'authentification est retourné.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 description="Token d'authentification"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Connexion échouée"
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
    public function login(Request $request)
    {
        try {
            // Authentifier l'utilisateur
            $credentials = $request->validate([
                'login' => 'required|max:50',
                'password' => 'required|max:255'
            ]);

            if (!Auth::attempt($credentials)) {
                return response()->json(["error" => USER_LOGIN_FAILED_MSG], UNAUTHORIZED);
            }

            $user = $request->user();
            // Générer un token d'authentification
            $token = $user->createToken('AuthToken')->plainTextToken;
            // Retourner la réponse avec le token
            return response()->json(['token' => $token], CREATED);
        } catch (ValidationException $e) {
            return response()->json(['error' => INVALID_DATA_MSG], BAD_REQUEST);
        } catch (Exception $exe) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/signout",
     *     tags={"Authentification"},
     *     summary="Déconnexion de l'utilisateur",
     *     description="Déconnecte l'utilisateur en révoquant tous ses jetons d'authentification.",
     *     security={{"Token": {}}},
     *     @OA\Response(
     *         response=204,
     *         description="Déconnexion réussie - Les jetons d'authentification ont été révoqués.",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Échec de la déconnexion - Utilisateur non authentifié."
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur."
     *     ),
     * )
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(["error" => USER_LOGOUT_FAILED_MSG], UNAUTHORIZED);
            }
            $user->tokens()->delete();
            return response()->noContent();
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     tags={"Utilisateurs"},
     *     summary="Récupère un utilisateur par son ID",
     *     description="Récupère les informations d'un utilisateur en fonction de son ID.",
     *     security={{"Token": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur à récupérer",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Utilisateur récupéré avec succès",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Interdit - Vous n'avez pas les permissions"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé - L'ID fourni ne correspond à aucun utilisateur"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur"
     *     )
     * )
     */
    public function show(int $id)
    {
        try {
            $user = $this->userRepository->getById($id);

            return (new UserResource($user))->response()->setStatusCode(OK);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->userRepository->delete($id);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    public function index()
    {
        try {
            $users = $this->userRepository->getAll();

            return UserResource::collection($users)->response()->setStatusCode(OK);
        } catch (Exception $ex) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/users/{id}/password",
     *     tags={"Utilisateurs"},
     *     summary="Mettre à jour le mot de passe de l'utilisateur",
     *     description="Permet à l'utilisateur de mettre à jour son mot de passe.",
     *     security={{"Token": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'utilisateur",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Nouveaux mots de passe de l'utilisateur",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"new_password", "password_confirmation"},
     *                 @OA\Property(
     *                     property="new_password",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Nouveau mot de passe de l'utilisateur"
     *                 ),
     *                 @OA\Property(
     *                     property="password_confirmation",
     *                     type="string",
     *                     maxLength=255,
     *                     description="Confirmation du nouveau mot de passe"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe mis à jour avec succès",
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
     *         description="Interdit - Vous n'avez pas les permissions"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Utilisateur non trouvé - L'ID de l'utilisateur n'existe pas"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Données invalides - Le nouveau mot de passe et sa confirmation ne correspondent pas"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur de serveur"
     *     )
     * )
     */
    public function updatePassword(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'new_password' => 'required|max:255',
                'password_confirmation' => 'required|max:255|same:new_password'
            ]);

            $user_id = $request->user()->id;
            $this->userRepository->updatePassword($user_id, $validatedData);

            return response()->json(['message' => UPDATED_MSG], OK);
        } catch (ValidationException $e) {
            return response()->json(['error' => INVALID_DATA_MSG], INVALID_DATA);
        } catch (ModelNotFoundException $ex) {
            return response()->json(['error' => NOT_FOUND_MSG], NOT_FOUND);
        } catch (Exception $exe) {
            return response()->json(['error' => SERVER_ERROR_MSG], SERVER_ERROR);
        }
    }
}
