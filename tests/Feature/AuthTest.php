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
        $response->assertJson(['message' => CREATED_MSG]);

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
        for ($i = 0; $i <= AUTH_THROTTLE; $i++) {
            $response = $this->postJson('/api/signup', [
                'login' => $i . 'user',
                'password' => 'password',
                'email' => $i . 'user@gmail.com',
                'last_name' => 'lastName',
                'first_name' => 'firstName'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);

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
        for ($i = 0; $i <= AUTH_THROTTLE; $i++) {
            $response = $this->postJson('/api/signin', [
                'login' => 'user',
                'password' => 'password'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);

        // Assure que le code de statut HTTP est celui de "Too Many Attempts" (429)
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }

    // DÉCONNEXION D'UN UTILISATEUR
    public function testLogoutUser()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
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
        $response->assertJson(['message' => UNAUTHENTICATED_MSG]);
    }

    public function testLogoutUserTooManyAttempts()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        // Effectue plusieurs requêtes GET pour se déconnecter
        for ($i = 0; $i <= AUTH_THROTTLE; $i++) {
            $response = $this->getJson('/api/signout', [
                'Accept' => 'application/json'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);

        // Assure que le code de statut HTTP est celui de "Too Many Attempts" (429)
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }

    public function testShowUser()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $response = $this->getJson("/api/users/{$user->id}", [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(OK);
    }

    public function testShowUserNotAuthenticated()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}", [
            'Accept' => 'application/json'
        ]);

        $response->assertJson(['message' => UNAUTHENTICATED_MSG]);
        $response->assertStatus(UNAUTHORIZED);
    }

    public function testShowOtherUser()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $id = $user->id + 1;

        $response = $this->getJson("/api/users/{$id}", [
            'Accept' => 'application/json'
        ]);

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testShowUserTooManyAttempts()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        for ($i = 0; $i <= DEFAULT_THROTTLE; $i++) {
            $response = $this->getJson("/api/users/{$user->id}", [
                'Accept' => 'application/json'
            ]);
        }

        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }

    public function testUpdatePasswordUser()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $newPassword = 'mdp1';

        $response = $this->putJson("/api/users/{$user->id}/password", [
            'new_password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertJson(['message' => UPDATED_MSG]);
        $response->assertStatus(OK);
    }

    public function testUpdatePasswordUserWithNonMatchingPasswords()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $response = $this->putJson("/api/users/{$user->id}/password", [
            'new_password' => 'mdp1',
            'password_confirmation' => 'mdp2'
        ]);

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testUpdatePasswordUserWithInvalidData()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $response = $this->putJson("/api/users/{$user->id}/password", [
            'new_password' => '',
            'password_confirmation' => ''
        ]);

        $response->assertJson(['error' => INVALID_DATA_MSG]);
        $response->assertStatus(BAD_REQUEST);
    }

    public function testUpdatePasswordUserNotAuthenticated()
    {
        $user = User::factory()->create();

        $newPassword = 'mdp1';

        $response = $this->putJson("/api/users/{$user->id}/password", [
            'new_password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertJson(['message' => UNAUTHENTICATED_MSG]);
        $response->assertStatus(UNAUTHORIZED);
    }

    public function testUpdatePasswordOtherUser()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $id = $user->id + 1;

        $newPassword = 'mdp1';

        $response = $this->putJson("/api/users/{$id}/password", [
            'new_password' => $newPassword,
            'password_confirmation' => $newPassword
        ]);

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testUpdatePasswordUserTooManyAttempts()
    {
        Sanctum::actingAs(
            $user = User::factory()->create()
        );

        $newPassword = 'mdp1';

        for ($i = 0; $i <= DEFAULT_THROTTLE; $i++) {
            $response = $this->putJson("/api/users/{$user->id}/password", [
                'new_password' => $newPassword,
                'password_confirmation' => $newPassword
            ]);
        }

        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }
}
