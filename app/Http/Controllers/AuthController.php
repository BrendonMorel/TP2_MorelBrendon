<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;

class AuthController extends Controller
{

    /**
     * @OA\Post(
     *     path="/api/signup",
     *     tags={"Users"},
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

            // Créer un nouvel utilisateur
            User::create([
                'login' => $request['login'],
                'password' => bcrypt($request['password']),
                'email' => $request['email'],
                'last_name' => $request['last_name'],
                'first_name' => $request['first_name'],
                'role_id' => USER // Rôle USER par défaut
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
 *
 * @OA\SecurityScheme(
 *     securityScheme="Token",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
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
            return response()->json(['error' => 'Server error'], SERVER_ERROR);
        }
    }
}
