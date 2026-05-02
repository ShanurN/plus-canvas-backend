<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MainCategoryResource;
use App\Models\MainCategory;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class MainCategoryController extends Controller
{
    #[OA\Get(
        path: "/api/main-categories",
        summary: "Get all active main categories",
        tags: ["Frontend - Categories"],
        responses: [
            new OA\Response(response: 200, description: "List of main categories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/MainCategoryResource")))
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $categories = MainCategory::where('is_active', true)
            ->with(['categories' => function($query) {
                $query->where('is_active', true)->orderBy('featured_order', 'asc');
            }])
            ->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return MainCategoryResource::collection($categories);
    }
}
