<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "CategoryResource",
    description: "Category resource representation",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "main_category_id", type: "integer", nullable: true),
        new OA\Property(property: "name", type: "string"),
        new OA\Property(property: "slug", type: "string"),
        new OA\Property(property: "category_type", type: "string", nullable: true),
        new OA\Property(property: "is_active", type: "boolean"),
        new OA\Property(property: "featured_order", type: "integer"),
        new OA\Property(property: "main_category", ref: "#/components/schemas/MainCategoryResource"),
        new OA\Property(property: "sub_categories", type: "array", items: new OA\Items(ref: "#/components/schemas/SubCategoryResource")),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'main_category_id' => $this->main_category_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'category_type' => $this->category_type,
            'is_active' => $this->is_active,
            'featured_order' => $this->featured_order,
            'main_category' => new MainCategoryResource($this->whenLoaded('mainCategory')),
            'sub_categories' => SubCategoryResource::collection($this->whenLoaded('subCategories')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
