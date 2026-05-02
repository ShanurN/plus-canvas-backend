<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class SubCategoryController extends Controller
{
    #[OA\Get(
        path: "/api/sub-categories",
        summary: "Get active subcategories",
        tags: ["Frontend - Categories"],
        parameters: [
            new OA\Parameter(name: "category_id", in: "query", schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "List of subcategories", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/SubCategoryResource")))
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = SubCategory::where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $subcategories = $query->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return SubCategoryResource::collection($subcategories);
    }
}
