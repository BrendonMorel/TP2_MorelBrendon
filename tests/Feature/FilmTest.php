<?php

namespace Tests\Feature;

use App\Models\Critic;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Language;
use App\Models\Film;
use App\Models\Role;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FilmTest extends TestCase
{
    use DatabaseMigrations;

    // CREATION D'UN FILM
    public function testCreateFilm()
    {
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $adminRole->id])
        );

        // Effectuer une requête POST pour créer un film
        $response = $this->postJson('/api/films', [
            'title' => 'Nom de film',
            'release_year' => 2000,
            'length' => 100,
            'description' => 'Ceci est une description de test...',
            'rating' => 'G',
            'special_features' => 'Trailers',
            'image' => 'Chemin image',
            'language_id' => Language::factory()->create()->id
        ]);

        $response->assertJson(['message' => CREATED_MSG]);
        $response->assertStatus(CREATED);
    }

    public function testCreateFilmUserNotAdmin()
    {
        $anyRole = Role::factory()->create(['name' => 'user']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $anyRole->id])
        );

        // Effectuer une requête POST pour créer un film
        $response = $this->postJson('/api/films', [
            'title' => 'Nom de film',
            'release_year' => 2000,
            'length' => 100,
            'description' => 'Ceci est une description de test...',
            'rating' => 'G',
            'special_features' => 'Trailers',
            'image' => 'Chemin image',
            'language_id' => Language::factory()->create()->id
        ]);

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testCreateFilmWithAdminRoleWithMissingField()
    {
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $adminRole->id])
        );

        // Effectuer une requête POST pour créer un film
        $response = $this->postJson('/api/films', [
            'title' => 'Nom de film',
            'release_year' => 2000,
            'length' => 100,
            // 'description' => 'Ceci est une description de test...', // Manquant
            'rating' => 'G',
            'special_features' => 'Trailers',
            'image' => 'Chemin image',
            'language_id' => Language::factory()->create()->id
        ]);

        $response->assertStatus(INVALID_DATA);
    }

    public function testCreateFilmTooManyAttempts()
    {
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $adminRole->id])
        );

        // Effectuer plusieurs requêtes POST pour créer un film
        for ($i = 0; $i <= 60; $i++) {
            $response = $this->postJson('/api/films', [
                'title' => 'Nom de film',
                'release_year' => 2000,
                'length' => 100,
                'description' => 'Ceci est une description de test...',
                'rating' => 'G',
                'special_features' => 'Trailers',
                'image' => 'Chemin image',
                'language_id' => Language::factory()->create()->id
            ]);
        }

        $response->assertJson(['message' => TOO_MANY_ATTEMPTS_MSG]);
        $response->assertStatus(TOO_MANY_ATTEMPTS);
    }

    // SUPPRESSION D'UN FILM
    public function testDeleteFilm()
    {
        $film = Film::factory()->create();
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $adminRole->id])
        );

        // Effectuer une requête DELETE pour supprimer un film
        $response = $this->deleteJson("/api/films/{$film->id}");

        $response->assertStatus(NO_CONTENT);
    }

    public function testDeleteFilmWithUserNotAdmin()
    {
        $film = Film::factory()->create();
        $anyRole = Role::factory()->create(['name' => 'user']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $anyRole->id])
        );

        // Effectuer une requête DELETE pour supprimer un film
        $response = $this->deleteJson("/api/films/{$film->id}");

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testDeleteFilmWithAdminRoleWithFilmHavingRelations()
    {
        // Authentification de l'utilisateur avec Sanctum
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);
        Sanctum::actingAs(
            $user = User::factory()->create(['role_id' => $adminRole->id])
        );

        $film = Film::factory()->create();

        // Crée une critique liée au film
        Critic::factory()->create(['film_id' => $film->id, 'user_id' => $user->id]);

        // Effectuer une requête DELETE pour supprimer un film
        $response = $this->deleteJson("/api/films/{$film->id}");

        $response->assertJson(['error' => FORBIDDEN_MSG]);
        $response->assertStatus(FORBIDDEN);
    }

    public function testDeleteFilmWithNonExistentFilm()
    {
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs(
            User::factory()->create(['role_id' => $adminRole->id])
        );

        // Effectuer une requête DELETE pour supprimer un film
        $response = $this->deleteJson("/api/films/9999");

        $response->assertJson(['error' => NOT_FOUND_MSG]);
        $response->assertStatus(NOT_FOUND);
    }

    public function testUpdateFilm()
    {
        // Crée un film
        $film = Film::factory()->create();

        // Crée des données valides pour la mise à jour du film
        $updatedData = [
            'title' => 'Nouveau titre',
            'release_year' => 2022,
            'length' => 120,
            'description' => 'Nouvelle description',
            'rating' => 'PG',
            'special_features' => 'Deleted Scenes',
            'image' => 'Nouveau chemin',
            'language_id' => Language::factory()->create()->id
        ];

        // Authentification de l'utilisateur avec Sanctum
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);
        Sanctum::actingAs($user);

        // Effectue une requête PUT pour mettre à jour le film
        $response = $this->putJson("/api/films/{$film->id}", $updatedData);

        // Vérifie que la réponse contient le message de mise à jour
        $response->assertJson(['message' => UPDATED_MSG]);

        // Vérifie que le code de statut HTTP est celui de "OK" (200)
        $response->assertStatus(OK);

        // Vérifie que les données du film ont été correctement mises à jour dans la base de données
        $this->assertDatabaseHas('films', $updatedData);
    }

    public function testUpdateFilmWithUserNotAdmin()
    {
        // Crée un film
        $film = Film::factory()->create();

        // Crée des données valides pour la mise à jour du film
        $updatedData = [
            'title' => 'Nouveau titre',
            'release_year' => 2022,
            'length' => 120,
            'description' => 'Nouvelle description',
            'rating' => 'PG',
            'special_features' => 'Deleted Scenes',
            'image' => 'Nouveau chemin',
            'language_id' => Language::factory()->create()->id
        ];

        // Authentification de l'utilisateur avec Sanctum
        $anyRole = Role::factory()->create(['id' => 1, 'name' => 'user']);
        $user = User::factory()->create(['role_id' => $anyRole->id]);
        Sanctum::actingAs($user);

        // Effectue une requête PUT pour mettre à jour le film
        $response = $this->putJson("/api/films/{$film->id}", $updatedData);

        // Vérifie que la réponse contient le message de mise à jour
        $response->assertJson(['error' => FORBIDDEN_MSG]);

        // Vérifie que le code de statut HTTP est celui "FORBIDDEN" (403)
        $response->assertStatus(FORBIDDEN);
    }

    public function testUpdateFilmWithNonExistentFilm()
    {
        // Crée des données valides pour la mise à jour du film
        $updatedData = [
            'title' => 'Nouveau titre',
            'release_year' => 2022,
            'length' => 120,
            'description' => 'Nouvelle description',
            'rating' => 'PG',
            'special_features' => 'Deleted Scenes',
            'image' => 'Nouveau chemin',
            'language_id' => Language::factory()->create()->id
        ];

        // Authentification de l'utilisateur avec Sanctum
        $adminRole = Role::factory()->create(['id' => 2, 'name' => 'admin']);
        $user = User::factory()->create(['role_id' => $adminRole->id]);
        Sanctum::actingAs($user);

        // Effectue une requête PUT pour mettre à jour le film
        $response = $this->putJson("/api/films/9999", $updatedData);

        // Vérifie que la réponse contient le message de mise à jour
        $response->assertJson(['error' => NOT_FOUND_MSG]);

        // Vérifie que le code de statut HTTP est celui "NOT_FOUND" (404)
        $response->assertStatus(NOT_FOUND);
    }
}
