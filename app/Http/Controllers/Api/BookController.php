<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Books",
    description: "API pro správu knih"
)]
class BookController extends Controller
{
    /**
     * @throws \JsonException
     */
    #[OA\Get(
        path: "/api/books",
        operationId: "getBooksList",
        description: "Vrátí seznam všech knih s možností filtrování, řazení a stránkování",
        summary: "Seznam všech knih",
        tags: ["Books"]
    )]
    #[OA\Parameter(
        name: "page",
        description: "Číslo stránky",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer",
            default: 1
        )
    )]
    #[OA\Parameter(
        name: "per_page",
        description: "Počet položek na stránku",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer",
            default: 15
        )
    )]
    #[OA\Parameter(
        name: "sort",
        description: "Sloupec pro řazení",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
            default: "created_at",
            enum: ["title", "published_at", "created_at"]
        )
    )]
    #[OA\Parameter(
        name: "order",
        description: "Směr řazení",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
            default: "desc",
            enum: ["asc", "desc"]
        )
    )]
    #[OA\Parameter(
        name: "title",
        description: "Filtrovat podle názvu",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\Parameter(
        name: "author_id",
        description: "Filtrovat podle ID autora",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer"
        )
    )]
    #[OA\Parameter(
        name: "category_id",
        description: "Filtrovat podle ID kategorie",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "integer"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Úspěšná operace",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: "data",
                    type: "array",
                    items: new OA\Items(type: "object")
                ),
                new OA\Property(property: "links", type: "object"),
                new OA\Property(property: "meta", type: "object")
            ],
            type: "object"
        )
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        // Cache klíč na základě všech parametrů požadavku
        $cacheKey = 'books:' . md5(json_encode($request->all(), JSON_THROW_ON_ERROR));

        // Načtení z cache nebo z databáze, cache 10 minut
        return Cache::remember($cacheKey, 600, static function () use ($request) {
            // Základní query
            $query = Book::with(['authors', 'categories']);

            // Filtry
            if ($request->has('title')) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            if ($request->has('author_id')) {
                $query->whereHas('authors', function ($q) use ($request) {
                    $q->where('authors.id', $request->author_id);
                });
            }

            if ($request->has('category_id')) {
                $query->whereHas('categories', function ($q) use ($request) {
                    $q->where('categories.id', $request->category_id);
                });
            }

            // Řazení
            $sort = (string)$request->input('sort', 'created_at');
            $order = (string)$request->input('order', 'desc');
            $allowedSortFields = ['title', 'published_at', 'created_at'];

            if (in_array($sort, $allowedSortFields)) {
                $query->orderBy($sort, $order);
            }

            // Stránkování
            $perPage = (int)$request->input('per_page', 15);
            return BookResource::collection($query->paginate($perPage));
        });
    }

    #[OA\Post(
        path: "/api/books",
        operationId: "storeBook",
        description: "Vytvoří novou knihu a vrátí její data",
        summary: "Vytvoření nové knihy",
        tags: ["Books"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["title"],
            properties: [
                new OA\Property(property: "title", type: "string", example: "Harry Potter"),
                new OA\Property(property: "isbn", type: "string", example: "978-80-00-05942-8"),
                new OA\Property(property: "published_at", type: "string", format: "date", example: "2022-01-15"),
                new OA\Property(property: "description", type: "string", example: "Popis knihy..."),
                new OA\Property(property: "page_count", type: "integer", example: 320),
                new OA\Property(property: "language", type: "string", example: "cs"),
                new OA\Property(
                    property: "author_ids",
                    type: "array",
                    items: new OA\Items(type: "integer"),
                    example: [1, 2]
                ),
                new OA\Property(
                    property: "category_ids",
                    type: "array",
                    items: new OA\Items(type: "integer"),
                    example: [1, 3]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Kniha byla úspěšně vytvořena",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: "Validační chyba"
    )]
    public function store(BookRequest $request): BookResource
    {
        $book = Book::create($request->validated());

        // Přiřazení autorů
        if ($request->has('author_ids')) {
            $book->authors()->attach($request->author_ids);
        }

        // Přiřazení kategorií
        if ($request->has('category_ids')) {
            $book->categories()->attach($request->category_ids);
        }

        // Invalidace cache
        $this->invalidateBooksCache();

        return new BookResource($book->load(['authors', 'categories']));
    }

    /**
     * Invaliduje všechny cache klíče související s knihami
     */
    private function invalidateBooksCache(): void
    {
        // Pro jednoduchost odstraníme všechny klíče, které začínají 'books:'
        $keys = Cache::get('cache_keys:books', []);
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    #[OA\Get(
        path: "/api/books/{id}",
        operationId: "getBookById",
        description: "Vrátí detailní informace o knize podle ID",
        summary: "Detail knihy",
        tags: ["Books"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID knihy",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "integer"
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Úspěšná operace",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Kniha nebyla nalezena"
    )]
    public function show(Book $book): BookResource
    {
        $cacheKey = 'book:' . $book->id;

        return Cache::remember($cacheKey, 600, function () use ($book) {
            return new BookResource($book->load(['authors', 'categories', 'ratings', 'comments']));
        });
    }

    #[OA\Put(
        path: "/api/books/{id}",
        operationId: "updateBook",
        description: "Aktualizuje knihu podle ID a vrátí její data",
        summary: "Aktualizace knihy",
        tags: ["Books"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID knihy",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "integer"
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", example: "Harry Potter (aktualizováno)"),
                new OA\Property(property: "isbn", type: "string", example: "978-80-00-05942-8"),
                new OA\Property(property: "published_at", type: "string", format: "date", example: "2022-01-15"),
                new OA\Property(property: "description", type: "string", example: "Nový popis knihy..."),
                new OA\Property(property: "page_count", type: "integer", example: 320),
                new OA\Property(property: "language", type: "string", example: "cs"),
                new OA\Property(
                    property: "author_ids",
                    type: "array",
                    items: new OA\Items(type: "integer"),
                    example: [1, 3]
                ),
                new OA\Property(
                    property: "category_ids",
                    type: "array",
                    items: new OA\Items(type: "integer"),
                    example: [2, 4]
                )
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Kniha byla úspěšně aktualizována",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Kniha nebyla nalezena"
    )]
    #[OA\Response(
        response: 422,
        description: "Validační chyba"
    )]
    public function update(BookRequest $request, Book $book): BookResource
    {
        $book->update($request->validated());

        // Aktualizace autorů
        if ($request->has('author_ids')) {
            $book->authors()->sync($request->author_ids);
        }

        // Aktualizace kategorií
        if ($request->has('category_ids')) {
            $book->categories()->sync($request->category_ids);
        }

        // Invalidace cache
        $this->invalidateBooksCache();
        Cache::forget('book:' . $book->id);

        return new BookResource($book->load(['authors', 'categories']));
    }

    #[OA\Delete(
        path: "/api/books/{id}",
        operationId: "deleteBook",
        description: "Smaže knihu podle ID",
        summary: "Smazání knihy",
        tags: ["Books"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID knihy",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "integer"
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Kniha byla úspěšně smazána"
    )]
    #[OA\Response(
        response: 404,
        description: "Kniha nebyla nalezena"
    )]
    public function destroy(Book $book): Response
    {
        $book->delete();

        // Invalidace cache
        $this->invalidateBooksCache();
        Cache::forget('book:' . $book->id);

        return response()->noContent();
    }
}
