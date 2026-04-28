<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    #[OA\Get(
        path: "/api/banners",
        summary: "Get list of all active banners",
        tags: ["Frontend - Banners"],
        responses: [
            new OA\Response(
                response: 200,
                description: "List of banners",
                content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/BannerResource"))
            )
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return BannerResource::collection($banners);
    }

    #[OA\Get(
        path: "/api/banners/{id}",
        summary: "Get single banner details",
        tags: ["Frontend - Banners"],
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
}
