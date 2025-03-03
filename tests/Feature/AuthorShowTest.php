<?php

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorShowTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_full_name_is_correctly_generated(): void
    {
        // Vytvoříme autora
        $author = Author::factory()->create([
            'name'    => 'Karel',
            'surname' => 'Čapek'
        ]);

        // Zavoláme API
        $response = $this->getJson(route('authors.show', ['author' => $author->id]));

        // Ověříme, že full_name je správně vygenerováno
        $response->assertStatus(200)
            ->assertJsonPath('data.full_name', 'Karel Čapek');
    }

    public function test_get_non_existent_author_returns_404(): void
    {
        // Pokus o načtení neexistujícího autora
        $response = $this->getJson(route('authors.show', ['author' => 99999]));

        // Očekáváme, že API vrátí 404
        $response->assertStatus(404);
    }

    public function test_author_show_json_structure(): void
    {
        // Vytvoření autora
        $author = Author::factory()->create();

        // Zavolání API pomocí routy
        $response = $this->getJson(route('authors.show', ['author' => $author->id]));

        // Ověření, že JSON odpověď má správnou strukturu
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'surname',
                    'full_name',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function test_author_show_returns_correct_content_type_header(): void
    {
        // Vytvoření autora
        $author = Author::factory()->create();

        // Zavolání API pomocí routy
        $response = $this->getJson(route('authors.show', ['author' => $author->id]));

        // Ověření, že hlavička Content-Type obsahuje 'application/json'
        $response->assertHeader('Content-Type', 'application/json');
    }

    public function test_author_show_timestamps_are_valid_format(): void
    {
        // Vytvoření autora
        $author = Author::factory()->create();

        // Zavolání API pomocí routy
        $response = $this->getJson(route('authors.show', ['author' => $author->id]));

        // Načtení časových razítek z odpovědi
        $createdAt = $response->json('data.created_at');
        $updatedAt = $response->json('data.updated_at');

        // Ověření, že časová razítka odpovídají očekávanému formátu (např. ISO 8601)
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $createdAt);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}:\d{2}/', $updatedAt);
    }
}
