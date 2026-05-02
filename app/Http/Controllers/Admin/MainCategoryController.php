<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MainCategory\StoreMainCategoryRequest;
use App\Http\Requests\MainCategory\UpdateMainCategoryRequest;
use App\Http\Resources\MainCategoryResource;
use App\Models\MainCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class MainCategoryController extends Controller
{
    #[OA\Get(
        path: "/api/admin/main-categories",
        summary: "Get list of main categories with filters",
        tags: ["Admin - Main Categories"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "name", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "featured_order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of main categories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/MainCategoryResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = MainCategory::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('featured_order')) {
            $query->where('featured_order', $request->featured_order);
        }

        $categories = $query->orderBy('featured_order', 'asc')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return MainCategoryResource::collection($categories);
    }

    #[OA\Post(
        path: "/api/admin/main-categories",
        summary: "Create a new main category",
        tags: ["Admin - Main Categories"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["name"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "slug", type: "string"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Main category created", content: new OA\JsonContent(ref: "#/components/schemas/MainCategoryResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreMainCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $category = MainCategory::create($data);

        return (new MainCategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/main-categories/{id}",
        summary: "Get single main category",
        tags: ["Admin - Main Categories"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Main category details", content: new OA\JsonContent(ref: "#/components/schemas/MainCategoryResource")),
            new OA\Response(response: 404, description: "Main category not found")
        ]
    )]
    public function show(MainCategory $main_category): MainCategoryResource
    {
        return new MainCategoryResource($main_category->load('categories'));
    }

    #[OA\Post(
        path: "/api/admin/main-categories/{id}",
        summary: "Update main category",
        description: "Use POST with _method=PUT for multipart/form-data updates",
        tags: ["Admin - Main Categories"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "_method", type: "string", example: "PUT"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "slug", type: "string"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Main category updated", content: new OA\JsonContent(ref: "#/components/schemas/MainCategoryResource")),
            new OA\Response(response: 404, description: "Main category not found")
        ]
    )]
    public function update(UpdateMainCategoryRequest $request, MainCategory $main_category): MainCategoryResource
    {
        $data = $request->validated();

        $main_category->update($data);

        return new MainCategoryResource($main_category);
    }

    #[OA\Delete(
        path: "/api/admin/main-categories/{id}",
        summary: "Delete main category",
        tags: ["Admin - Main Categories"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Main category deleted")
        ]
    )]
    public function destroy(MainCategory $main_category): JsonResponse
    {
        $main_category->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/main-categories/reorder",
        summary: "Batch update main categories order",
        tags: ["Admin - Main Categories"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "featured_order", type: "integer"),
                        ]
                    ))
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Order updated")
        ]
    )]
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:main_categories,id'],
            'items.*.featured_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $item) {
            $category = MainCategory::find($item['id']);
            $category->update(array_filter($item, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
        }

        return response()->json(['message' => 'Main categories updated successfully']);
    }
}
