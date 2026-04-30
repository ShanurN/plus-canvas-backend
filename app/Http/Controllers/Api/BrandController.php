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
            ->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return BrandResource::collection($brands);
    }
}
