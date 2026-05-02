<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    #[OA\Get(
        path: "/api/categories",
        summary: "Get active categories with optional main_category filter",
        tags: ["Frontend - Categories"],
        parameters: [
            new OA\Parameter(name: "main_category_id", in: "query", schema: new OA\Schema(type: "integer")),
            new OA\Parameter(name: "category_type", in: "query", schema: new OA\Schema(type: "string")),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of categories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/CategoryResource")))
        ]
    )]
    public function index(\Illuminate\Http\Request $request): AnonymousResourceCollection
    {
        $query = Category::where('is_active', true);

        if ($request->filled('main_category_id')) {
            $query->where('main_category_id', $request->main_category_id);
        }

        if ($request->filled('category_type')) {
            $query->where('category_type', $request->category_type);
        }

        $categories = $query->with(['subCategories' => function($q) {
                $q->where('is_active', true)->orderBy('featured_order', 'asc');
            }])
            ->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }
}
