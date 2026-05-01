<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CanvasSizeResource;
use App\Models\CanvasSize;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CanvasSizeController extends Controller
{
    /**
     * Display a listing of active canvas sizes.
     */
    public function index(): AnonymousResourceCollection
    {
        $sizes = CanvasSize::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        return CanvasSizeResource::collection($sizes);
    }
}
