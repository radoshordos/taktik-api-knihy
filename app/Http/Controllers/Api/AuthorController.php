<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Authors",
    description: "API pro správu autorů"
)]
class AuthorController extends Controller
{
    #[OA\Get(
        path: "/api/authors",
        operationId: "getAuthorsList",
        description: "Vrátí seznam všech autorů s možností filtrování, řazení a stránkování",
        summary: "Seznam všech autorů",
        tags: ["Authors"]
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
            default: "surname",
            enum: ["name", "surname", "created_at"]
        )
    )]
    #[OA\Parameter(
        name: "order",
        description: "Směr řazení",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string",
            default: "asc",
            enum: ["asc", "desc"]
        )
    )]
    #[OA\Parameter(
        name: "name",
        description: "Filtrovat podle jména",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string"
        )
    )]
    #[OA\Parameter(
        name: "surname",
        description: "Filtrovat podle příjmení",
        in: "query",
        required: false,
        schema: new OA\Schema(
            type: "string"
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
        $query = Author::query();

        // Filtry
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('surname')) {
            $query->where('surname', 'like', '%' . $request->surname . '%');
        }

        // Řazení
        $sort = (string)$request->input('sort', 'surname');
        $order = (string)$request->input('order', 'asc');
        $allowedSortFields = ['name', 'surname', 'created_at'];

        if (in_array($sort, $allowedSortFields, true)) {
            $query->orderBy($sort, $order);
        }

        // Stránkování
        $perPage = (int)$request->input('per_page', 15);

        return AuthorResource::collection($query->paginate($perPage));
    }

    #[OA\Post(
        path: "/api/authors",
        operationId: "storeAuthor",
        description: "Vytvoří nového autora a vrátí jeho data",
        summary: "Vytvoření nového autora",
        tags: ["Authors"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "surname"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "J.K."),
                new OA\Property(property: "surname", type: "string", example: "Rowlingová"),
                new OA\Property(property: "birthdate", type: "string", format: "date", example: "1965-07-31"),
                new OA\Property(property: "biography", type: "string", example: "Britská spisovatelka..."),
                new OA\Property(property: "nationality", type: "string", example: "britská")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Autor byl úspěšně vytvořen",
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
    public function store(AuthorRequest $request): AuthorResource
    {
        $author = Author::create($request->validated());
        return new AuthorResource($author);
    }

    #[OA\Get(
        path: "/api/authors/{id}",
        operationId: "getAuthorById",
        description: "Vrátí detailní informace o autorovi podle ID",
        summary: "Detail autora",
        tags: ["Authors"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID autora",
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
        description: "Autor nebyl nalezen"
    )]
    public function show(Author $author): AuthorResource
    {
        return new AuthorResource($author->load(['books', 'ratings', 'comments']));
    }

    #[OA\Put(
        path: "/api/authors/{id}",
        operationId: "updateAuthor",
        description: "Aktualizuje autora podle ID a vrátí jeho data",
        summary: "Aktualizace autora",
        tags: ["Authors"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID autora",
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
                new OA\Property(property: "name", type: "string", example: "J.K."),
                new OA\Property(property: "surname", type: "string", example: "Rowling"),
                new OA\Property(property: "birthdate", type: "string", format: "date", example: "1965-07-31"),
                new OA\Property(property: "biography", type: "string", example: "Aktualizovaná biografie..."),
                new OA\Property(property: "nationality", type: "string", example: "britská")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Autor byl úspěšně aktualizován",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Autor nebyl nalezen"
    )]
    #[OA\Response(
        response: 422,
        description: "Validační chyba"
    )]

    public function update(AuthorRequest $request, Author $author): AuthorResource
    {
        $author->update($request->validated());
        return new AuthorResource($author);
    }

    #[OA\Delete(
        path: "/api/authors/{id}",
        operationId: "deleteAuthor",
        description: "Smaže autora podle ID",
        summary: "Smazání autora",
        tags: ["Authors"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID autora",
        in: "path",
        required: true,
        schema: new OA\Schema(
            type: "integer"
        )
    )]
    #[OA\Response(
        response: 204,
        description: "Autor byl úspěšně smazán"
    )]
    #[OA\Response(
        response: 404,
        description: "Autor nebyl nalezen"
    )]

    public function destroy(Author $author): Response
    {
        $author->delete();
        return response()->noContent();
    }
}
