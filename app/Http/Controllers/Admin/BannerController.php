<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\StoreBannerRequest;
use App\Http\Requests\Banner\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    #[OA\Get(
        path: "/api/admin/banners",
        summary: "Get list of all banners (Admin)",
        tags: ["Banners"],
        security: [["apiAuth" => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of all banners",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/BannerResource"))
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return BannerResource::collection($banners);
    }

    #[OA\Get(
        path: "/api/admin/banners/{id}",
        summary: "Get single banner details (Admin)",
        tags: ["Banners"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Banner details", content: new OA\JsonContent(ref: "#/components/schemas/BannerResource")),
            new OA\Response(response: 404, description: "Banner not found")
        ]
    )]
    public function show(Banner $banner): BannerResource
    {
        return new BannerResource($banner);
    }

    #[OA\Post(
        path: "/api/admin/banners",
        summary: "Create a new banner",
        tags: ["Banners"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: "multipart/form-data",
                schema: new OA\Schema(
                    required: ["image"],
                    properties: [
                        new OA\Property(property: "title", type: "string"),
                        new OA\Property(property: "description", type: "string"),
                        new OA\Property(property: "image", type: "string", format: "binary"),
                        new OA\Property(property: "url", type: "string"),
                        new OA\Property(property: "order", type: "integer"),
                        new OA\Property(property: "is_active", type: "boolean"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Banner created", content: new OA\JsonContent(ref: "#/components/schemas/BannerResource")),
            new OA\Response(response: 422, description: "Validation error")
        ]
    )]
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('banners', 'public');
            $data['image_url'] = asset('storage/' . $data['image']);
        }

        $banner = Banner::create($data);

        return (new BannerResource($banner))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Post(
        path: "/api/admin/banners/{id}",
        summary: "Update an existing banner",
        description: "Note: Use POST with _method=PUT because of PHP multipart/form-data limitations",
        tags: ["Banners"],
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
                        new OA\Property(property: "url", type: "string"),
                        new OA\Property(property: "order", type: "integer"),
                        new OA\Property(property: "is_active", type: "boolean"),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Banner updated", content: new OA\JsonContent(ref: "#/components/schemas/BannerResource")),
            new OA\Response(response: 404, description: "Banner not found")
        ]
    )]
    public function update(UpdateBannerRequest $request, Banner $banner): BannerResource
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')->store('banners', 'public');
            $data['image_url'] = asset('storage/' . $data['image']);
        }

        $banner->update($data);

        return new BannerResource($banner);
    }

    #[OA\Delete(
        path: "/api/admin/banners/{id}",
        summary: "Delete a banner",
        tags: ["Banners"],
        security: [["apiAuth" => []]],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 204, description: "Banner deleted"),
            new OA\Response(response: 404, description: "Banner not found")
        ]
    )]
    public function destroy(Banner $banner): JsonResponse
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();

        return response()->json(null, 204);
    }

    #[OA\Post(
        path: "/api/admin/banners/reorder",
        summary: "Update banners order",
        tags: ["Banners"],
        security: [["apiAuth" => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "orders", type: "array", items: new OA\Items(
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
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'exists:banners,id'],
            'orders.*.order' => ['required', 'integer'],
        ]);

        foreach ($request->orders as $item) {
            Banner::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['message' => 'Order updated successfully']);
    }
}
