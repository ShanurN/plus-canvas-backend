<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountResource;
use App\Models\Discount;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class DiscountController extends Controller
{
    #[OA\Get(
        path: "/api/discounts",
        summary: "Get active discounts",
        tags: ["Frontend - Discounts"],
        responses: [
            new OA\Response(response: 200, description: "List of active discounts")
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $discounts = Discount::where('is_active', true)
            ->orderBy('order')
            ->orderBy('id', 'desc')
            ->get();

        return DiscountResource::collection($discounts);
    }

    #[OA\Get(
        path: "/api/discounts/{id}",
        summary: "Get single active discount details",
        tags: ["Frontend - Discounts"],
        parameters: [new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer"))],
        responses: [
            new OA\Response(response: 200, description: "Discount details"),
            new OA\Response(response: 404, description: "Discount not found")
        ]
    )]
    public function show(int $id): DiscountResource
    {
        $discount = Discount::where('is_active', true)->findOrFail($id);

        return new DiscountResource($discount);
    }
}
