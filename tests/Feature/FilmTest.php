<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Language;
use App\Models\Film;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FilmTest extends TestCase
{
    use DatabaseMigrations;

    // CREATION D'UN FILM
    public function testCreateFilm()
    {
        $adminUser = User::factory()->create(['role_id' => 2]);

        // Authentification de l'utilisateur avec Sanctum
        Sanctum::actingAs($adminUser);

        // Effectuer une requête POST pour créer un utilisateur
        $response = $this->postJson('/api/films', [
            'title' => 'Nom de film',
            'release_year' => 2000,
            'length' => 100,
            'description' => 'Ceci est une description de test...',
            'rating' => 'G',
            'speacial_features' => 'Trailers',
            'image' => 'Chemin image',
            'language_id' => 1 // Existe grâce au seed
        ]);

        // Assure que la réponse contient le message de création de l'utilisateur
        $response->assertJson(['message' => CREATED_MSG]);

        // Assure que le code de statut HTTP est celui de "Created" (201)
        $response->assertStatus(CREATED);
    }

    // AFFICHAGE DES FILMS
    public function testIndexFilm()
    {
        $response = $this->getJson('/api/films', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(OK);
    }
}
