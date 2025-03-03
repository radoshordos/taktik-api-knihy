<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_authors(): void
    {
        // Vytvoření několika autorů pro test
        Author::factory(5)->create();

        // Zavolání API
        $response = $this->getJson('/api/authors');

        // Ověření odpovědi
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'surname', 'full_name', 'created_at', 'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_get_single_author(): void
    {
        // Vytvoření autora
        $author = Author::factory()->create();

        // Zavolání API
        $response = $this->getJson('/api/authors/' . $author->id);

        // Ověření odpovědi
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id'      => $author->id,
                    'name'    => $author->name,
                    'surname' => $author->surname
                ]
            ]);
    }

    public function test_can_create_author(): void
    {
        // Data pro vytvoření nového autora
        $authorData = [
            'name'        => 'Karel',
            'surname'     => 'Čapek',
            'birthdate'   => '1890-01-09',
            'biography'   => 'Český spisovatel, intelektuál, novinář a překladatel.',
            'nationality' => 'česká'
        ];

        // Zavolání API
        $response = $this->postJson('/api/authors', $authorData);

        // Ověření odpovědi
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name'        => 'Karel',
                    'surname'     => 'Čapek',
                    'nationality' => 'česká'
                ]
            ]);

        // Ověření, že autor byl vytvořen v databázi
        $this->assertDatabaseHas('authors', [
            'name'    => 'Karel',
            'surname' => 'Čapek'
        ]);
    }

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

    public function test_can_delete_author(): void
    {
        // Vytvoření autora
        $author = Author::factory()->create();

        // Zavolání API
        $response = $this->deleteJson('/api/authors/' . $author->id);

        // Ověření odpovědi
        $response->assertStatus(204);

        // Ověření, že autor byl smazán
        $this->assertDatabaseMissing('authors', [
            'id' => $author->id
        ]);
    }
}
