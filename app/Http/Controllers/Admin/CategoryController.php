<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[OA\Get(
        path: "/api/admin/categories",
        summary: "Get list of categories with filters",
        tags: ["Admin - Categories"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "name", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_featured", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "is_most_searched", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "featured_order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "most_searched_order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "per_page", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of categories")
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Category::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->is_featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('is_most_searched')) {
            $query->where('is_most_searched', filter_var($request->is_most_searched, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('featured_order')) {
            $query->where('featured_order', $request->featured_order);
        }

        if ($request->filled('most_searched_order')) {
            $query->where('most_searched_order', $request->most_searched_order);
        }

        $categories = $query->orderBy('id', 'desc')->paginate($request->integer('per_page', 15));

        return CategoryResource::collection($categories);
    }

    #[OA\Post(
        path: "/api/admin/categories",
        summary: "Create a new category",
        tags: ["Admin - Categories"],
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
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "is_featured", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Category created"),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category = Category::create($data);

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/categories/{id}",
        summary: "Get single category",
        tags: ["Admin - Categories"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Category details")
        ]
    )]
    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    #[OA\Post(
        path: "/api/admin/categories/{id}",
        summary: "Update category",
        description: "Use POST with _method=PUT for multipart/form-data updates",
        tags: ["Admin - Categories"],
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
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "is_featured", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Category updated"),
            new OA\Response(response: 404, description: "Category not found")
        ]
    )]
    public function update(UpdateCategoryRequest $request, Category $category): CategoryResource
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        $category->update($data);

        return new CategoryResource($category);
    }

    #[OA\Delete(
        path: "/api/admin/categories/{id}",
        summary: "Delete category",
        tags: ["Admin - Categories"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Category deleted")
        ]
    )]
    public function destroy(Category $category): JsonResponse
    {
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/categories/reorder",
        summary: "Batch update categories order/blocks",
        tags: ["Admin - Categories"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "is_featured", type: "boolean"),
                            new OA\Property(property: "featured_order", type: "integer"),
                            new OA\Property(property: "is_most_searched", type: "boolean"),
                            new OA\Property(property: "most_searched_order", type: "integer"),
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
            'items.*.id' => ['required', 'exists:categories,id'],
            'items.*.is_featured' => ['nullable', 'boolean'],
            'items.*.featured_order' => ['nullable', 'integer'],
            'items.*.is_most_searched' => ['nullable', 'boolean'],
            'items.*.most_searched_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $item) {
            $category = Category::find($item['id']);
            $category->update(array_filter($item, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
        }

        return response()->json(['message' => 'Categories updated successfully']);
    }
}
