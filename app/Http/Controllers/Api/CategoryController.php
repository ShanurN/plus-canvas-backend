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
        path: "/api/categories/most-searched",
        summary: "Get most searched categories for home page",
        tags: ["Frontend - Categories"],
        responses: [
            new OA\Response(response: 200, description: "List of most searched categories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/CategoryResource")))
        ]
    )]
    public function mostSearched(): AnonymousResourceCollection
    {
        $categories = Category::where('is_active', true)
            ->where('is_most_searched', true)
            ->orderBy('most_searched_order')
            ->get();

        return CategoryResource::collection($categories);
    }

    #[OA\Get(
        path: "/api/categories",
        summary: "Get all active categories",
        tags: ["Frontend - Categories"],
        responses: [
            new OA\Response(response: 200, description: "List of categories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/CategoryResource")))
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::where('is_active', true)
            ->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }
}
