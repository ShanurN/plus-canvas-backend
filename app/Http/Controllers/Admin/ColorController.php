<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Color\StoreColorRequest;
use App\Http\Requests\Color\UpdateColorRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ColorController extends Controller
{
    #[OA\Get(
        path: "/api/admin/colors",
        summary: "Get list of colors with filters",
        tags: ["Admin - Colors"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "name", in: "query", schema: new OA\Schema(type: "string")),
            new OA\Parameter(name: "featured_order", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of colors", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/ColorResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Color::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('featured_order')) {
            $query->where('featured_order', $request->featured_order);
        }

        $colors = $query->orderBy('featured_order', 'asc')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return ColorResource::collection($colors);
    }

    #[OA\Post(
        path: "/api/admin/colors",
        summary: "Create a new color",
        tags: ["Admin - Colors"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["name"],
                    properties: [
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "hex_code", type: "string"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Color created", content: new OA\JsonContent(ref: "#/components/schemas/ColorResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreColorRequest $request): JsonResponse
    {
        $data = $request->validated();

        $color = Color::create($data);

        return (new ColorResource($color))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/colors/{id}",
        summary: "Get single color",
        tags: ["Admin - Colors"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Color details", content: new OA\JsonContent(ref: "#/components/schemas/ColorResource")),
            new OA\Response(response: 404, description: "Color not found")
        ]
    )]
    public function show(Color $color): ColorResource
    {
        return new ColorResource($color);
    }

    #[OA\Post(
        path: "/api/admin/colors/{id}",
        summary: "Update color",
        description: "Use POST with _method=PUT for multipart/form-data updates",
        tags: ["Admin - Colors"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: "_method", type: "string", example: "PUT"),
                        new OA\Property(property: "name", type: "string"),
                        new OA\Property(property: "hex_code", type: "string"),
                        new OA\Property(property: "is_active", type: "boolean"),
                        new OA\Property(property: "featured_order", type: "integer"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Color updated", content: new OA\JsonContent(ref: "#/components/schemas/ColorResource")),
            new OA\Response(response: 404, description: "Color not found")
        ]
    )]
    public function update(UpdateColorRequest $request, Color $color): ColorResource
    {
        $data = $request->validated();

        $color->update($data);

        return new ColorResource($color);
    }

    #[OA\Delete(
        path: "/api/admin/colors/{id}",
        summary: "Delete color",
        tags: ["Admin - Colors"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Color deleted")
        ]
    )]
    public function destroy(Color $color): JsonResponse
    {
        $color->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/colors/reorder",
        summary: "Batch update colors order",
        tags: ["Admin - Colors"],
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
            'items.*.id' => ['required', 'exists:colors,id'],
            'items.*.featured_order' => ['nullable', 'integer'],
        ]);

        foreach ($request->items as $item) {
            $color = Color::find($item['id']);
            $color->update(array_filter($item, fn($key) => $key !== 'id', ARRAY_FILTER_USE_KEY));
        }

        return response()->json(['message' => 'Colors updated successfully']);
    }
}
