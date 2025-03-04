<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
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
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        // Ověření, že autor byl smazán
        $this->assertDatabaseMissing('authors', [
            'id' => $author->id
        ]);
    }

    public function test_delete_non_existent_author_returns_404(): void
    {
        $response = $this->deleteJson(route('authors.destroy', ['author' => 99999]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
