<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

#[OA\Tag(
    name: "Categories",
    description: "API pro správu kategorií"
)]

class CategoryController extends Controller
{
    #[OA\Get(
        path: "/api/categories",
        operationId: "getCategoriesList",
        description: "Vrátí seznam všech kategorií s možností filtrování a řazení",
        summary: "Seznam všech kategorií",
        tags: ["Categories"]
    )]
    #[OA\Parameter(
        name: "name",
        description: "Filtrovat podle názvu",
        in: "query",
        required: false,
        schema: new OA\Schema(type: "string")
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
                )
            ]
        )
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Category::query();

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        $query->orderBy('name', 'asc');

        return CategoryResource::collection($query->get());
    }

    #[OA\Post(
        path: "/api/categories",
        operationId: "storeCategory",
        description: "Vytvoří novou kategorii a vrátí její data",
        summary: "Vytvoření nové kategorie",
        tags: ["Categories"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Fantasy"),
                new OA\Property(property: "description", type: "string", example: "Knihy z oblasti fantasy...")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: "Kategorie byla úspěšně vytvořena",
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

    public function store(CategoryRequest $request): CategoryResource
    {
        $category = Category::create($request->validated());
        return new CategoryResource($category);
    }

    #[OA\Get(
        path: "/api/categories/{id}",
        operationId: "getCategoryById",
        description: "Vrátí detailní informace o kategorii podle ID",
        summary: "Detail kategorie",
        tags: ["Categories"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID kategorie",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
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
        description: "Kategorie nebyla nalezena"
    )]

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category->load('books'));
    }

    #[OA\Put(
        path: "/api/categories/{id}",
        operationId: "updateCategory",
        description: "Aktualizuje kategorii podle ID a vrátí její data",
        summary: "Aktualizace kategorie",
        tags: ["Categories"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID kategorie",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "name", type: "string", example: "Fantasy & Sci-Fi"),
                new OA\Property(property: "description", type: "string", example: "Aktualizovaný popis kategorie...")
            ]
        )
    )]
    #[OA\Response(
        response: 200,
        description: "Kategorie byla úspěšně aktualizována",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: "Kategorie nebyla nalezena"
    )]
    #[OA\Response(
        response: 422,
        description: "Validační chyba"
    )]

    public function update(CategoryRequest $request, Category $category): CategoryResource
    {
        $category->update($request->validated());

        return new CategoryResource($category);
    }

    #[OA\Delete(
        path: "/api/categories/{id}",
        operationId: "deleteCategory",
        description: "Smaže kategorii podle ID",
        summary: "Smazání kategorie",
        tags: ["Categories"]
    )]
    #[OA\Parameter(
        name: "id",
        description: "ID kategorie",
        in: "path",
        required: true,
        schema: new OA\Schema(type: "integer")
    )]
    #[OA\Response(
        response: 204,
        description: "Kategorie byla úspěšně smazána"
    )]
    #[OA\Response(
        response: 404,
        description: "Kategorie nebyla nalezena"
    )]
    public function destroy(Category $category): Response
    {
        $category->delete();
        return response()->noContent();
    }
}
