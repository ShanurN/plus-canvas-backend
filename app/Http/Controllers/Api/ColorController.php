<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ColorController extends Controller
{
    #[OA\Get(
        path: "/api/colors",
        summary: "Get all active colors",
        tags: ["Frontend - Colors"],
        responses: [
            new OA\Response(response: 200, description: "List of colors", content: new OA\JsonContent(type: "array", items: new OA\Items(ref: "#/components/schemas/ColorResource")))
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $colors = Color::where('is_active', true)
            ->orderBy('featured_order', 'asc')
            ->orderBy('name')
            ->get();

        return ColorResource::collection($colors);
    }
}
