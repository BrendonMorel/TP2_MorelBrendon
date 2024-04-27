<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use Tests\TestCase;



class AuthTest extends TestCase
{
    use DatabaseMigrations;

    // CREATION D'UN UTILISATEUR
    public function testCreateUser()
    {
        // Effectuer une requête POST pour créer un utilisateur
        $response = $this->postJson('/api/signup', [
            'login' => 'JDoe',
            'password' => 'password',
            'email' => 'jdoe@gmail.com',
            'last_name' => 'Doe',
            'first_name' => 'John'
        ]);

        // Assure que la réponse contient le message de création de l'utilisateur
        $response->assertJson(['message' => USER_CREATED_MSG]);

        // Assure que le code de statut HTTP est celui de "Created" (201)
        $response->assertStatus(CREATED);
    }

    public function testCreateUserAlreadyExists()
    {
        // Créer un utilisateur avec les données spécifiées
        $this->postJson('/api/signup', [
            'login' => 'LPaquin',
            'password' => 'password',
            'email' => 'lpaquin@gmail.com',
            'last_name' => 'Paquin',
            'first_name' => 'Laurie'
        ])->assertStatus(CREATED);

        // Effectuer une autre requête POST pour créer l'utilisateur avec les mêmes données
        $response = $this->postJson('/api/signup', [
            'login' => 'LPaquin',
            'password' => 'password',
            'email' => 'lpaquin@gmail.com',
            'last_name' => 'Paquin',
            'first_name' => 'Laurie'
        ]);

        // Assure que la réponse contient le message d'erreur attendu
        $response->assertJson(['error' => INVALID_DATA_MSG]);

        // Assure que le code de statut HTTP est celui de "Bad Request" (400)
        $response->assertStatus(BAD_REQUEST);
    }

    public function testCreateUserMissingDatas()
    {
        // Effectue une requête POST pour créer un utilisateur avec des données manquantes
        $response = $this->postJson('/api/signup', [
            'login' => 'MHamel',
            'email' => 'mhamel@gmail.com',
            'last_name' => 'Hamel',
            'first_name' => 'Michel'
        ]);

        // Assure que la réponse contient le message d'erreur attendu
        $response->assertJson(['error' => INVALID_DATA_MSG]);

        // Assure que le code de statut HTTP est celui de "Bad Request" (400)
        $response->assertStatus(BAD_REQUEST);
    }

    public function testCreateUserInvalidDatas()
    {
        // Effectue une requête POST pour créer un utilisateur avec des données invalides
        $response = $this->postJson('/api/signup', [
            'login' => 'MHamel',
            'password' => '', // Mot de passe requis
            'email' => 'mhamel@gmail.com',
            'last_name' => 'Hamel',
            'first_name' => 'Michel'
        ]);

        // Assure que la réponse contient le message d'erreur attendu
        $response->assertJson(['error' => INVALID_DATA_MSG]);

        // Assure que le code de statut HTTP est celui de "Bad Request" (400)
        $response->assertStatus(BAD_REQUEST);
    }

    public function testCreateUserTooManyAttempts()
    {
        // Effectue plusieurs requêtes POST pour créer des utilisateurs
        for ($i = 0; $i <= 5; $i++) {
            $response = $this->postJson('/api/signup', [
                'login' => $i . 'user',
                'password' => 'password',
                'email' => $i . 'user@gmail.com',
                'last_name' => 'lastName',
                'first_name' => 'firstName'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => 'Too Many Attempts.']);

        // Assure que le code de statut HTTP est celui de "Too Many Attempts" (429)
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }

    // CONNEXION D'UN UTILISATEUR
    public function testLoginUser()
    {
        // Crée un utilisateur
        $this->postJson('/api/signup', [
            'login' => 'JDoe',
            'password' => 'password',
            'email' => 'jdoe@gmail.com',
            'last_name' => 'Doe',
            'first_name' => 'John'
        ])->assertStatus(CREATED);

        // Effectue une requête POST pour la connexion de l'utilisateur
        $response = $this->postJson('/api/signin', [
            'login' => 'JDoe',
            'password' => 'password'
        ]);

        // Assure que la réponse contient un jeton non vide
        $response->assertJsonStructure(['token']);

        // Assure que le code de statut HTTP est celui de "Created" (201)
        $response->assertStatus(CREATED);
    }

    public function testLoginUserDoesNotExist()
    {
        // Effectue une requête POST avec un utilisateur qui n'existe pas
        $response = $this->postJson('/api/signin', [
            'login' => 'username',
            'password' => 'password'
        ]);

        // Assure que la réponse contient le message d'erreur attendu
        $response->assertJson(['error' => USER_LOGIN_FAILED_MSG]);

        // Assure que le code de statut HTTP est celui de "Unauthorized" (401)
        $response->assertStatus(UNAUTHORIZED);
    }

    public function testLoginUserMissingDatas()
    {
        // Effectue une requête POST sans le champ 'login'
        $response = $this->postJson('/api/signin', [
            'password' => 'password'
        ]);

        // Assure que la réponse contient le message d'erreur attendu
        $response->assertJson(['error' => INVALID_DATA_MSG]);

        // Assure que le code de statut HTTP est celui de "Bad Request" (400)
        $response->assertStatus(BAD_REQUEST);
    }

    public function testLoginUserTooManyAttempts()
    {
        // Effectue plusieurs requêtes POST pour se connecter
        for ($i = 0; $i <= 5; $i++) {
            $response = $this->postJson('/api/signin', [
                'login' => 'user',
                'password' => 'password'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => 'Too Many Attempts.']);

        // Assure que le code de statut HTTP est celui de "Too Many Attempts" (429)
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }

    // DÉCONNEXION D'UN UTILISATEUR
    public function testLogoutUser()
    {
        Sanctum::actingAs(
            $user = User::factory()->create(),
            ['*']
        );

        // Effectue une requête GET pour se déconnecter
        $response = $this->getJson('/api/signout', [
            'Accept' => 'application/json'
        ]);

        // Assure que la réponse est vide
        $response->assertNoContent();
    }

    public function testLogoutUserNotLoggedIn()
    {
        // Effectue une requête GET pour se déconnecter
        $response = $this->getJson('/api/signout');

        // Assure que le code de statut HTTP est UNAUTHORIZED (401)
        $response->assertStatus(UNAUTHORIZED);

        // Assure que le contenu de la réponse correspond à l'erreur attendue
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function testLogoutUserTooManyAttempts()
    {
        Sanctum::actingAs(
            $user = User::factory()->create(),
            ['*']
        );

        // Effectue plusieurs requêtes GET pour se déconnecter
        for ($i = 0; $i <= 5; $i++) {
            $response = $this->getJson('/api/signout', [
                'Accept' => 'application/json'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => 'Too Many Attempts.']);

        // Assure que le code de statut HTTP est celui de "Too Many Attempts" (429)
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }
}
