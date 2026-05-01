<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CanvasFormat extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The sizes that belong to the format.
     */
    public function sizes(): BelongsToMany
    {
        return $this->belongsToMany(CanvasSize::class, 'canvas_format_size')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order', 'asc');
    }
}
