<?php

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorDeleteTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_delete_non_existent_author_returns_404(): void
    {
        // Pokus o smazání neexistujícího autora
        $response = $this->deleteJson(route('authors.destroy', ['author' => 99999]));

        // Ověření, že API vrátí 404
        $response->assertStatus(404);
    }

}
