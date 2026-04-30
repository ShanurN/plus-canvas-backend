<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class BrandController extends Controller
{
    #[OA\Get(
        path: "/api/brands/featured",
        summary: "Get featured brands for home page",
        tags: ["Frontend - Brands"],
        responses: [
            new OA\Response(response: 200, description: "List of featured brands", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/BrandResource")))
        ]
    )]
    public function featured(): AnonymousResourceCollection
    {
        $brands = Brand::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('featured_order')
            ->get();

        return BrandResource::collection($brands);
    }

    #[OA\Get(
        path: "/api/brands/most-searched",
        summary: "Get most searched brands for home page",
        tags: ["Frontend - Brands"],
        responses: [
            new OA\Response(response: 200, description: "List of most searched brands", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/BrandResource")))
        ]
    )]
    public function mostSearched(): AnonymousResourceCollection
    {
        $brands = Brand::where('is_active', true)
            ->where('is_most_searched', true)
            ->orderBy('most_searched_order')
            ->get();

        return BrandResource::collection($brands);
    }

    #[OA\Get(
        path: "/api/brands",
        summary: "Get all active brands",
        tags: ["Frontend - Brands"],
        responses: [
            new OA\Response(response: 200, description: "List of brands", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/BrandResource")))
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }
}
