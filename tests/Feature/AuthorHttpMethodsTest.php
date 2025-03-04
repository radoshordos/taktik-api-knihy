<?php

namespace Tests\Feature;

use App\Models\Author;
use Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthorHttpMethodsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsupported_http_methods(): void
    {
        // Test: Volání PUT na kolekci autorů (endpoint /api/authors) není podporováno
        $response = $this->putJson(route('authors.index'));
        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);

        // Test: Volání POST na konkrétního autora (endpoint /api/authors/{id}) není podporováno
        $author = Author::factory()->create();
        $response = $this->postJson(route('authors.show', $author));
        $response->assertStatus(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function test_api_enforces_rate_limiting(): void
    {
        $author = Author::factory()->create();
        for ($i = 0; $i < config('api.rate_limit'); $i++) {
            $response = $this->getJson(route('authors.show', $author->id));
            $response->assertStatus(Response::HTTP_OK);
        }

        $response = $this->getJson(route('authors.show', $author->id));
        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);

        $response->assertHeader('X-RateLimit-Limit');
        $response->assertHeader('X-RateLimit-Remaining');
        $response->assertHeader('Retry-After');
    }

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush(); // Vymažeme cache, aby test nezašel předchozími hodnotami rate limiteru.
    }
}
