<?php

namespace Tests\Feature;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_all_books(): void
    {
        // Vytvoření několika knih pro test
        Book::factory(5)->create();

        // Zavolání API
        $response = $this->getJson('/api/books');

        // Ověření odpovědi
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'title', 'isbn', 'created_at', 'updated_at'
                    ]
                ],
                'links',
                'meta'
            ]);
    }

    public function test_can_get_single_book(): void
    {
        // Vytvoření knihy
        $book = Book::factory()->create();

        // Zavolání API
        $response = $this->getJson('/api/books/' . $book->id);

        // Ověření odpovědi
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id'    => $book->id,
                    'title' => $book->title
                ]
            ]);
    }

    public function test_can_create_book(): void
    {
        // Vytvoření autora a kategorie pro propojení
        $author = Author::factory()->create();
        $category = Category::factory()->create();

        // Data pro vytvoření nové knihy
        $bookData = [
            'title'        => 'Testovací kniha',
            'isbn'         => '978-80-7381-931-6',
            'published_at' => '2023-01-15',
            'description'  => 'Popis testovací knihy',
            'author_ids'   => [$author->id],
            'category_ids' => [$category->id]
        ];

        // Zavolání API
        $response = $this->postJson('/api/books', $bookData);

        // Ověření odpovědi
        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'title' => 'Testovací kniha',
                    'isbn'  => '978-80-7381-931-6'
                ]
            ]);

        // Ověření, že kniha byla vytvořena v databázi
        $this->assertDatabaseHas('books', [
            'title' => 'Testovací kniha',
            'isbn'  => '978-80-7381-931-6'
        ]);

        // Ověření vazeb
        $this->assertDatabaseHas('author_book', [
            'author_id' => $author->id,
            'book_id'   => $response->json('data.id')
        ]);

        $this->assertDatabaseHas('book_category', [
            'category_id' => $category->id,
            'book_id'     => $response->json('data.id')
        ]);
    }

    public function test_can_update_book(): void
    {
        // Vytvoření knihy
        $book = Book::factory()->create();

        // Data pro aktualizaci
        $updateData = [
            'title'       => 'Aktualizovaný název',
            'description' => 'Nový popis knihy'
        ];

        // Zavolání API
        $response = $this->putJson('/api/books/' . $book->id, $updateData);

        // Ověření odpovědi
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id'          => $book->id,
                    'title'       => 'Aktualizovaný název',
                    'description' => 'Nový popis knihy'
                ]
            ]);

        // Ověření změn v databázi
        $this->assertDatabaseHas('books', [
            'id'          => $book->id,
            'title'       => 'Aktualizovaný název',
            'description' => 'Nový popis knihy'
        ]);
    }

    public function test_can_delete_book(): void
    {
        // Vytvoření knihy
        $book = Book::factory()->create();

        // Zavolání API
        $response = $this->deleteJson('/api/books/' . $book->id);

        // Ověření odpovědi
        $response->assertStatus(204);

        // Ověření, že kniha byla smazána
        $this->assertDatabaseMissing('books', [
            'id' => $book->id
        ]);
    }
}
