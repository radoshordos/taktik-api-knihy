<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorUnsupportedMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsupported_http_methods(): void
    {
        // Test: Volání PUT na kolekci autorů (endpoint /api/authors) není podporováno
        $response = $this->putJson('/api/authors', []);
        $response->assertStatus(405);

        // Test: Volání POST na konkrétního autora (endpoint /api/authors/{id}) není podporováno
        $author = Author::factory()->create();
        $response = $this->postJson('/api/authors/' . $author->id, []);
        $response->assertStatus(405);
    }
}
