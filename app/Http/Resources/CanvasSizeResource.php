<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    title: "CanvasSizeResource",
    description: "Canvas size resource representation",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "width", type: "number", format: "float"),
        new OA\Property(property: "height", type: "number", format: "float"),
        new OA\Property(property: "unit", type: "string"),
        new OA\Property(property: "display_name", type: "string"),
        new OA\Property(property: "is_active", type: "boolean"),
        new OA\Property(property: "sort_order", type: "integer"),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class CanvasSizeResource extends JsonResource
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
            'width' => $this->width,
            'height' => $this->height,
            'unit' => $this->unit,
            'display_name' => $this->display_name,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
