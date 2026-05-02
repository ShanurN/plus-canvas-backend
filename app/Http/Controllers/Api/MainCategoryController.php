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
        parameters: [
            new OA\Parameter(name: "category_type", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of main categories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/MainCategoryResource")))
        ]
    )]
    public function index(\Illuminate\Http\Request $request): AnonymousResourceCollection
    {
        $query = MainCategory::where('is_active', true);

        if ($request->filled('category_type')) {
            $query->where('category_type', $request->category_type);
        }

        $categories = $query->with(['categories' => function($query) {
                $query->where('is_active', true)->orderBy('featured_order', 'asc');
            }])
            ->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return MainCategoryResource::collection($categories);
    }
}
