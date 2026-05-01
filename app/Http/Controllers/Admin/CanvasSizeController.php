<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CanvasSize\StoreCanvasSizeRequest;
use App\Http\Requests\CanvasSize\UpdateCanvasSizeRequest;
use App\Http\Resources\CanvasSizeResource;
use App\Models\CanvasSize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class CanvasSizeController extends Controller
{
    #[OA\Get(
        path: "/api/admin/canvas-sizes",
        summary: "Get list of canvas sizes",
        tags: ["Admin - Canvas Sizes"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of sizes", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/CanvasSizeResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $sizes = CanvasSize::orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return CanvasSizeResource::collection($sizes);
    }

    #[OA\Post(
        path: "/api/admin/canvas-sizes",
        summary: "Create a new canvas size",
        tags: ["Admin - Canvas Sizes"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["width", "height"],
                properties: [
                    new OA\Property(property: "name", type: "string", nullable: true),
                    new OA\Property(property: "width", type: "number", format: "float"),
                    new OA\Property(property: "height", type: "number", format: "float"),
                    new OA\Property(property: "unit", type: "string", default: "cm"),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                    new OA\Property(property: "sort_order", type: "integer", default: 0),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Size created", content: new OA\JsonContent(ref: "#/components/schemas/CanvasSizeResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreCanvasSizeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $size = CanvasSize::create($data);

        return (new CanvasSizeResource($size))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/canvas-sizes/{id}",
        summary: "Get single canvas size",
        tags: ["Admin - Canvas Sizes"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Size details", content: new OA\JsonContent(ref: "#/components/schemas/CanvasSizeResource")),
            new OA\Response(response: 404, description: "Size not found")
        ]
    )]
    public function show(CanvasSize $canvasSize): CanvasSizeResource
    {
        return new CanvasSizeResource($canvasSize);
    }

    #[OA\Put(
        path: "/api/admin/canvas-sizes/{id}",
        summary: "Update canvas size",
        tags: ["Admin - Canvas Sizes"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", nullable: true),
                    new OA\Property(property: "width", type: "number", format: "float"),
                    new OA\Property(property: "height", type: "number", format: "float"),
                    new OA\Property(property: "unit", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                    new OA\Property(property: "sort_order", type: "integer"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Size updated", content: new OA\JsonContent(ref: "#/components/schemas/CanvasSizeResource")),
            new OA\Response(response: 404, description: "Size not found")
        ]
    )]
    public function update(UpdateCanvasSizeRequest $request, CanvasSize $canvasSize): CanvasSizeResource
    {
        $canvasSize->update($request->validated());
        return new CanvasSizeResource($canvasSize);
    }

    #[OA\Delete(
        path: "/api/admin/canvas-sizes/{id}",
        summary: "Delete canvas size",
        tags: ["Admin - Canvas Sizes"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Size deleted")
        ]
    )]
    public function destroy(CanvasSize $canvasSize): JsonResponse
    {
        $canvasSize->delete();
        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/canvas-sizes/reorder",
        summary: "Batch update sizes order",
        tags: ["Admin - Canvas Sizes"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "items", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "sort_order", type: "integer"),
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
            'items.*.id' => ['required', 'exists:canvas_sizes,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->items as $item) {
            CanvasSize::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Canvas sizes reordered successfully']);
    }
}
