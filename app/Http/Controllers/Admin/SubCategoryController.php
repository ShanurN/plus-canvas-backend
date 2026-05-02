<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategory\StoreSubCategoryRequest;
use App\Http\Requests\SubCategory\UpdateSubCategoryRequest;
use App\Http\Resources\SubCategoryResource;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class SubCategoryController extends Controller
{
    #[OA\Get(
        path: "/api/admin/sub-categories",
        summary: "Get list of subcategories with filters",
        tags: ["Admin - Subcategories"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "name", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "category_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "featured_order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of subcategories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/SubCategoryResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SubCategory::with(['category']);

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('featured_order')) {
            $query->where('featured_order', $request->featured_order);
        }

        $subcategories = $query->orderBy('featured_order', 'asc')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return SubCategoryResource::collection($subcategories);
    }

    #[OA\Post(
        path: "/api/admin/sub-categories",
        summary: "Create a new subcategory",
        tags: ["Admin - Subcategories"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["name", "category_id"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "category_id", type: "integer"),
                        new OA\Property(property: "slug", type: "string"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Subcategory created", content: new OA\JsonContent(ref: "#/components/schemas/SubCategoryResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreSubCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $subcategory = SubCategory::create($data);

        return (new SubCategoryResource($subcategory->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/sub-categories/{id}",
        summary: "Get single subcategory",
        tags: ["Admin - Subcategories"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Subcategory details", content: new OA\JsonContent(ref: "#/components/schemas/SubCategoryResource")),
            new OA\Response(response: 404, description: "Subcategory not found")
        ]
    )]
    public function show(SubCategory $sub_category): SubCategoryResource
    {
        return new SubCategoryResource($sub_category->load('category'));
    }

    #[OA\Put(
        path: "/api/admin/sub-categories/{id}",
        summary: "Update subcategory",
        description: "Use POST with _method=PUT for multipart/form-data updates",
        tags: ["Admin - Subcategories"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "_method", type: "string", example: "PUT"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "category_id", type: "integer"),
                        new OA\Property(property: "slug", type: "string"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Subcategory updated", content: new OA\JsonContent(ref: "#/components/schemas/SubCategoryResource")),
            new OA\Response(response: 404, description: "Subcategory not found")
        ]
    )]
    public function update(UpdateSubCategoryRequest $request, SubCategory $sub_category): SubCategoryResource
    {
        $data = $request->validated();

        $sub_category->update($data);

        return new SubCategoryResource($sub_category);
    }

    #[OA\Delete(
        path: "/api/admin/sub-categories/{id}",
        summary: "Delete subcategory",
        tags: ["Admin - Subcategories"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Subcategory deleted")
        ]
    )]
    public function destroy(SubCategory $sub_category): JsonResponse
    {
        $sub_category->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/sub-categories/reorder",
        summary: "Batch update subcategories order",
        tags: ["Admin - Subcategories"],
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
            'items.*.id' => ['required', 'exists:sub_categories,id'],
            'items.*.featured_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $item) {
            $subcategory = SubCategory::find($item['id']);
            $subcategory->update(array_filter($item, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
        }

        return response()->json(['message' => 'Subcategories updated successfully']);
    }
}
