<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CanvasFormatResource;
use App\Models\CanvasFormat;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CanvasFormatController extends Controller
{
    /**
     * Display a listing of active canvas formats with their sizes.
     */
    public function index(): AnonymousResourceCollection
    {
        $formats = CanvasFormat::where('is_active', true)
            ->with(['sizes' => function ($query) {
                $query->where('canvas_sizes.is_active', true);
            }])
            ->orderBy('sort_order', 'asc')
            ->get();

        return CanvasFormatResource::collection($formats);
    }
}
