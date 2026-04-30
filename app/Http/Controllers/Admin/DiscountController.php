<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Discount\StoreDiscountRequest;
use App\Http\Requests\Discount\UpdateDiscountRequest;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class DiscountController extends Controller
{
    #[OA\Get(
        path: "/api/admin/discounts",
        summary: "Get list of discounts with filters",
        tags: ["Admin - Discounts"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "title", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "is_active", in: "query", schema: new OA\Schema(type: "boolean")),
            new OA\Parameter(name: "order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of discounts", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/DiscountResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Discount::query();

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('order')) {
            $query->where('order', $request->order);
        }

        $discounts = $query->orderBy('order')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return DiscountResource::collection($discounts);
    }

    #[OA\Post(
        path: "/api/admin/discounts",
        summary: "Create a new discount",
        tags: ["Admin - Discounts"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["title"],
                    properties: [
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Discount created", content: new OA\JsonContent(ref: "#/components/schemas/DiscountResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreDiscountRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('discounts', 'public');
            $data['image_url'] = asset('storage/' . $data['image']);
        }

        $discount = Discount::create($data);

        return (new DiscountResource($discount))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/discounts/{id}",
        summary: "Get single discount",
        tags: ["Admin - Discounts"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Discount details", content: new OA\JsonContent(ref: "#/components/schemas/DiscountResource")),
            new OA\Response(response: 404, description: "Discount not found")
        ]
    )]
    public function show(Discount $discount): DiscountResource
    {
        return new DiscountResource($discount);
    }

    #[OA\Post(
        path: "/api/admin/discounts/{id}",
        summary: "Update discount",
        description: "Use POST with _method=PUT for multipart/form-data updates",
        tags: ["Admin - Discounts"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "_method", type: "string", example: "PUT"),
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Discount updated", content: new OA\JsonContent(ref: "#/components/schemas/DiscountResource")),
            new OA\Response(response: 404, description: "Discount not found")
        ]
    )]
    public function update(UpdateDiscountRequest $request, Discount $discount): DiscountResource
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($discount->image) {
                Storage::disk('public')->delete($discount->image);
            }
            $data['image'] = $request->file('image')->store('discounts', 'public');
            $data['image_url'] = asset('storage/' . $data['image']);
        }

        $discount->update($data);

        return new DiscountResource($discount);
    }

    #[OA\Delete(
        path: "/api/admin/discounts/{id}",
        summary: "Delete discount",
        tags: ["Admin - Discounts"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 204, description: "Discount deleted")
        ]
    )]
    public function destroy(Discount $discount): JsonResponse
    {
        if ($discount->image) {
            Storage::disk('public')->delete($discount->image);
        }

        $discount->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/discounts/reorder",
        summary: "Batch update discounts order",
        tags: ["Admin - Discounts"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "order", type: "integer"),
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
            'items.*.id' => ['required', 'exists:discounts,id'],
            'items.*.order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $item) {
            $discount = Discount::find($item['id']);
            $discount->update(array_filter($item, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
        }

        return response()->json(['message' => 'Discounts updated successfully']);
    }
}
