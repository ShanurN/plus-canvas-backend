<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CanvasFormat\StoreCanvasFormatRequest;
use App\Http\Requests\CanvasFormat\UpdateCanvasFormatRequest;
use App\Http\Resources\CanvasFormatResource;
use App\Models\CanvasFormat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class CanvasFormatController extends Controller
{
    #[OA\Get(
        path: "/api/admin/canvas-formats",
        summary: "Get list of canvas formats",
        tags: ["Admin - Canvas Formats"],
        security: [["apiAuth" => []]],
        parameters: [
            new OA\Parameter(name: "limit", in: "query", schema: new OA\Schema(type: "integer", default: 15)),
            new OA\Parameter(name: "offset", in: "query", schema: new OA\Schema(type: "integer", default: 0)),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of formats", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/CanvasFormatResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $formats = CanvasFormat::with('sizes')
            ->orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->skip($request->integer('offset', 0))
            ->take($request->integer('limit', 15))
            ->get();

        return CanvasFormatResource::collection($formats);
    }

    #[OA\Post(
        path: "/api/admin/canvas-formats",
        summary: "Create a new canvas format",
        tags: ["Admin - Canvas Formats"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name", "slug"],
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "slug", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean", default: true),
                    new OA\Property(property: "sort_order", type: "integer", default: 0),
                    new OA\Property(property: "sizes", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "sort_order", type: "integer"),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Format created", content: new OA\JsonContent(ref: "#/components/schemas/CanvasFormatResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreCanvasFormatRequest $request): JsonResponse
    {
        $data = $request->validated();
        $sizes = $data['sizes'] ?? [];
        unset($data['sizes']);

        $format = CanvasFormat::create($data);

        if (!empty($sizes)) {
            $syncData = [];
            foreach ($sizes as $size) {
                $syncData[$size['id']] = ['sort_order' => $size['sort_order'] ?? 0];
            }
            $format->sizes()->sync($syncData);
        }

        return (new CanvasFormatResource($format->load('sizes')))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: "/api/admin/canvas-formats/{id}",
        summary: "Get single canvas format",
        tags: ["Admin - Canvas Formats"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "Format details", content: new OA\JsonContent(ref: "#/components/schemas/CanvasFormatResource")),
            new OA\Response(response: 404, description: "Format not found")
        ]
    )]
    public function show(CanvasFormat $canvasFormat): CanvasFormatResource
    {
        return new CanvasFormatResource($canvasFormat->load('sizes'));
    }

    #[OA\Put(
        path: "/api/admin/canvas-formats/{id}",
        summary: "Update canvas format",
        tags: ["Admin - Canvas Formats"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string"),
                    new OA\Property(property: "slug", type: "string"),
                    new OA\Property(property: "is_active", type: "boolean"),
                    new OA\Property(property: "sort_order", type: "integer"),
                    new OA\Property(property: "sizes", type: "array", items: new OA\Items(
                        properties: [
                            new OA\Property(property: "id", type: "integer"),
                            new OA\Property(property: "sort_order", type: "integer"),
                        ]
                    )),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Format updated", content: new OA\JsonContent(ref: "#/components/schemas/CanvasFormatResource")),
            new OA\Response(response: 404, description: "Format not found")
        ]
    )]
    public function update(UpdateCanvasFormatRequest $request, CanvasFormat $canvasFormat): CanvasFormatResource
    {
        $data = $request->validated();
        $sizes = $data['sizes'] ?? null;
        unset($data['sizes']);

        $canvasFormat->update($data);

        if ($sizes !== null) {
            $syncData = [];
            foreach ($sizes as $size) {
                $syncData[$size['id']] = ['sort_order' => $size['sort_order'] ?? 0];
            }
            $canvasFormat->sizes()->sync($syncData);
        }

        return new CanvasFormatResource($canvasFormat->load('sizes'));
    }

    #[OA\Delete(
        path: "/api/admin/canvas-formats/{id}",
        summary: "Delete canvas format",
        tags: ["Admin - Canvas Formats"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(response: 204, description: "Format deleted")
        ]
    )]
    public function destroy(CanvasFormat $canvasFormat): JsonResponse
    {
        $canvasFormat->delete();
        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/canvas-formats/reorder",
        summary: "Batch update formats order",
        tags: ["Admin - Canvas Formats"],
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
            'items.*.id' => ['required', 'exists:canvas_formats,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($request->items as $item) {
            CanvasFormat::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Canvas formats reordered successfully']);
    }
}
