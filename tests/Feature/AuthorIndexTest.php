<?php

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorIndexTest extends TestCase
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

    public function test_authors_are_paginated(): void
    {
        // Vytvoříme dostatečné množství autorů
        Author::factory(30)->create();

        // Zavoláme API s parametrem stránky
        $response = $this->getJson(route('authors.index', ['page' => 2]));

        // Ověříme, že odpověď obsahuje správnou strukturu pro paginaci
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links' => ['first', 'last', 'prev', 'next'],
                'meta'  => [
                    'current_page',
                    'from',
                    'last_page',
                    'path',
                    'per_page',
                    'to',
                    'total'
                ]
            ])
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_index_returns_empty_collection_when_no_authors_exist(): void
    {
        // Žádní autoři nejsou vytvořeni
        $response = $this->getJson(route('authors.index'));

        // Ověříme strukturu a prázdné pole 'data'
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);

        $this->assertEmpty($response->json('data'));
    }

    public function test_index_returns_authors_with_correct_full_name(): void
    {
        // Vytvoříme autora s definovanými hodnotami
        $author = Author::factory()->create([
            'name'    => 'Karel',
            'surname' => 'Čapek'
        ]);

        // Zavoláme API a ověříme, že full_name je správně vygenerováno i v seznamu
        $response = $this->getJson(route('authors.index'));
        $response->assertStatus(200)
            ->assertJsonFragment([
                'full_name' => 'Karel Čapek'
            ]);
    }

    public function test_can_specify_per_page_parameter(): void
    {
        // Vytvoříme 25 autorů
        Author::factory(25)->create();

        // Požádáme API o stránku s 10 položkami na stránku pomocí parametru 'per_page'
        $response = $this->getJson(route('authors.index', ['per_page' => 10]));
        $response->assertStatus(200)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25);

        // Ověříme, že v datech je opravdu 10 záznamů
        $this->assertCount(10, $response->json('data'));
    }

    public function test_requesting_page_beyond_last_page_returns_empty_data(): void
    {
        // Vytvoříme několik autorů, např. 5
        Author::factory(5)->create();

        // Požádáme o stránku, která neobsahuje žádná data (např. stránka 2)
        $response = $this->getJson(route('authors.index', ['page' => 2]));
        $response->assertStatus(200);

        // Ověříme, že pole 'data' je prázdné
        $this->assertEmpty($response->json('data'));
    }



}
