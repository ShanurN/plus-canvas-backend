<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class BrandController extends Controller
{
    #[OA\Get(
        path: "/api/admin/brands",
        summary: "Get list of brands with filters",
        tags: ["Admin - Brands"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "name", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "featured_order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of brands", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/BrandResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Brand::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('featured_order')) {
            $query->where('featured_order', $request->featured_order);
        }

        $brands = $query->orderBy('featured_order', 'asc')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return BrandResource::collection($brands);
    }

    #[OA\Post(
        path: "/api/admin/brands",
        summary: "Create a new brand",
        tags: ["Admin - Brands"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "application/json",
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
            new OA\Response(response: 201, description: "Brand created", content: new OA\JsonContent(ref: "#/components/schemas/BrandResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $data = $request->validated();

        $brand = Brand::create($data);

        return (new BrandResource($brand))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/brands/{id}",
        summary: "Get single brand",
        tags: ["Admin - Brands"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Brand details", content: new OA\JsonContent(ref: "#/components/schemas/BrandResource")),
            new OA\Response(response: 404, description: "Brand not found")
        ]
    )]
    public function show(Brand $brand): BrandResource
    {
        return new BrandResource($brand);
    }

    #[OA\Put(
        path: "/api/admin/brands/{id}",
        summary: "Update brand",
        tags: ["Admin - Brands"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "application/json",
                schema: new OA\Schema(
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
            new OA\Response(response: 200, description: "Brand updated", content: new OA\JsonContent(ref: "#/components/schemas/BrandResource")),
            new OA\Response(response: 404, description: "Brand not found")
        ]
    )]
    public function update(UpdateBrandRequest $request, Brand $brand): BrandResource
    {
        $data = $request->validated();

        $brand->update($data);

        return new BrandResource($brand);
    }

    #[OA\Delete(
        path: "/api/admin/brands/{id}",
        summary: "Delete brand",
        tags: ["Admin - Brands"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 204, description: "Brand deleted")
        ]
    )]
    public function destroy(Brand $brand): JsonResponse
    {
        $brand->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/brands/reorder",
        summary: "Batch update brands order/blocks",
        tags: ["Admin - Brands"],
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
            'items.*.id' => ['required', 'exists:brands,id'],
            'items.*.featured_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $item) {
            $brand = Brand::find($item['id']);
            $brand->update(array_filter($item, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
        }

        return response()->json(['message' => 'Brands updated successfully']);
    }
}
