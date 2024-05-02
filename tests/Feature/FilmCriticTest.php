<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Film;
use App\Models\User;
use App\Models\Critic;
use Laravel\Sanctum\Sanctum;

class FilmCriticTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreateCritic()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'comment' => 'Voici un commentaire'
        ]);

        $response->assertJson(['message' => CREATED_MSG]);
        $response->assertStatus(CREATED);
    }

    public function testCreateCriticWithNoAuthenticatedUser()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'comment' => 'Voici un commentaire'
        ]);

        $response->assertJson(['message' => UNAUTHENTICATED_MSG]);
        $response->assertStatus(UNAUTHORIZED);
    }

    public function testCreateCriticWithMissingField()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3
        ]);

        $response->assertStatus(INVALID_DATA);
    }

    public function testCreateCriticWithInvalidData()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'comment' => 4, // Invalide
        ]);

        $response->assertStatus(INVALID_DATA);
    }

    public function testCreateCriticWithCriticLimitMiddleware()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Créez un film
        $film = Film::factory()->create();

        // Enregistrez une critique pour ce film avec cet utilisateur
        $existingCritic = Critic::factory()->create([
            'user_id' => $user->id,
            'film_id' => $film->id,
        ]);

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'comment' => 'Voici un commentaire'
        ]);

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testCreateCriticTooManyAttempts()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Effectue plusieurs requêtes POST pour créer des 'critics'
        for ($i = 0; $i <= DEFAULT_THROTTLE; $i++) {
            // Créez un film
            $film = Film::factory()->create();

            $response = $this->postJson('/api/films/{$film->id}/critics', [
                'score' => 3,
                'comment' => 'Voici un commentaire'
            ]);
        }

        // Assure que le message "Too Many Attempts" est retourné
        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);

        // Assure que le code de statut HTTP est celui de "Too Many Attempts" (429)
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }
}
