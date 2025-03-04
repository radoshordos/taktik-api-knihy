<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_author(): void
    {
        // Vytvoření autora
        $author = Author::factory()->create();

        // Data pro aktualizaci
        $updateData = [
            'name'      => $author->name,
            'surname'   => 'Nové příjmení',
            'biography' => 'Aktualizovaná biografie'
        ];

        // Zavolání API
        $response = $this->putJson('/api/authors/' . $author->id, $updateData);

        // Ověření odpovědi
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id'        => $author->id,
                    'name'      => $author->name,
                    'surname'   => 'Nové příjmení',
                    'biography' => 'Aktualizovaná biografie'
                ]
            ]);

        // Ověření změn v databázi
        $this->assertDatabaseHas('authors', [
            'id'        => $author->id,
            'surname'   => 'Nové příjmení',
            'biography' => 'Aktualizovaná biografie'
        ]);
    }

    public function test_update_non_existent_author_returns_404(): void
    {
        // Data pro aktualizaci, kdy autor neexistuje
        $updateData = [
            'name'    => 'Test',
            'surname' => 'Testovský'
        ];

        // Pokus o aktualizaci neexistujícího autora
        $response = $this->putJson(route('authors.update', ['author' => 999]), $updateData);

        // Ověření, že API vrátí 404
        $response->assertStatus(404);
    }
}
