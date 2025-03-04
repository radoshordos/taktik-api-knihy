<?php

namespace Tests\Feature;

use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorStoreTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_create_author_with_invalid_data_returns_validation_errors(): void
    {
        // Odeslání prázdných dat, kdy povinné hodnoty chybí
        $response = $this->postJson(route('authors.store'), []);

        // Ověření, že API vrátí 422 a chybová hlášení pro povinná pole
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'surname']);
    }

    public function test_create_author_with_invalid_birthdate_format_returns_validation_error(): void
    {
        // Data s neplatným formátem data narození
        $authorData = [
            'name'        => 'Jan',
            'surname'     => 'Novák',
            'birthdate'   => '31-02-2000', // Neplatný formát data
            'biography'   => 'Testovací biografie',
            'nationality' => 'Testland'
        ];

        // Zavoláme API pro vytvoření autora
        $response = $this->postJson('/api/authors', $authorData);

        // Ověříme, že API vrací chybu 422 a chybové hlášení pro pole 'birthdate'
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birthdate']);
    }

    public function test_mass_assignment_protection(): void
    {
        // Data pro vytvoření autora, včetně nepovolených polí, která by měla být ignorována
        $authorData = [
            'name'        => 'Test',
            'surname'     => 'User',
            'birthdate'   => '2000-01-01',
            'biography'   => 'Testovací biografie.',
            'nationality' => 'Testland',
            // Nepovolená pole
            'id'          => 999,
            'created_at'  => '2000-01-01 00:00:00',
            'updated_at'  => '2000-01-01 00:00:00',
        ];

        // Zavoláme API pro vytvoření autora
        $response = $this->postJson('/api/authors', $authorData);
        $response->assertStatus(201);

        // Ověříme, že autor nebyl vytvořen s poskytnutým 'id'
        $this->assertDatabaseMissing('authors', ['id' => 999]);

        // Získáme nově vytvořeného autora a ověříme, že hodnoty 'created_at' a 'updated_at' jsou automaticky generovány systémem
        $author = Author::first();
        $this->assertNotEquals('2000-01-01 00:00:00', $author->created_at->format('Y-m-d H:i:s'));
        $this->assertNotEquals('2000-01-01 00:00:00', $author->updated_at->format('Y-m-d H:i:s'));
    }

}
