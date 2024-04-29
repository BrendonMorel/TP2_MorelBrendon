<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Film;
use App\Models\Critic;
use App\Models\Language;
use Laravel\Sanctum\Sanctum;

class CriticTest extends TestCase
{
    use DatabaseMigrations;
   
    public function testStoreCriticWithNoAuthenticatedUser()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'comment' => 'Voici un commentaire',
            'user_id' => $user->id,
            'film_id' => $film->id
        ]);

        $response->assertJson(['message' => UNAUTHENTICATED_MSG]);
        $response->assertStatus(UNAUTHORIZED);
    }

    public function testStoreCriticWithNoExistingUserCritic()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'comment' => 'Voici un commentaire',
            'user_id' => $user->id,
            'film_id' => $film->id
        ]);

        $response->assertJson(['message' => CREATED_MSG]);
        $response->assertStatus(CREATED);
    }

    public function testStoreCriticWithMissingField()
    {
        // Créez un utilisateur
        $user = User::factory()->create();

        // Authentifiez l'utilisateur
        Sanctum::actingAs($user);

        // Créez un film
        $film = Film::factory()->create();

        $response = $this->postJson("/api/films/{$film->id}/critics", [
            'score' => 3,
            'user_id' => $user->id,
            'film_id' => $film->id
        ]);

        $response->assertStatus(INVALID_DATA);
    }

    public function testStoreCriticWithInvalidData()
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
            'user_id' => $user->id,
            'film_id' => $film->id
        ]);

        $response->assertStatus(INVALID_DATA);
    }

    public function testStoreCriticWithCriticLimitMiddleware()
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
            'comment' => 'Voici un commentaire',
            'user_id' => $user->id,
            'film_id' => $film->id
        ]);

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }
}
