<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "BannerResource",
    title: "Banner Resource",
    description: "Hero banner details",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Big Sale"),
        new OA\Property(property: "description", type: "string", example: "Up to 50% off"),
        new OA\Property(property: "image", type: "string", example: "banners/image.jpg"),
        new OA\Property(property: "image_url", type: "string", example: "http://localhost:8000/storage/banners/image.jpg"),
        new OA\Property(property: "url", type: "string", example: "/shop/sales"),
        new OA\Property(property: "order", type: "integer", example: 1),
        new OA\Property(property: "is_active", type: "boolean", example: true),
    ]
)]
class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'image_url' => $this->image ? asset('storage/' . $this->image) : null,
            'url' => $this->url,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
