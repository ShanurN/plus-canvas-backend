<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CanvasSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'width',
        'height',
        'unit',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'width' => 'float',
        'height' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The formats that this size belongs to.
     */
    public function formats(): BelongsToMany
    {
        return $this->belongsToMany(CanvasFormat::class, 'canvas_format_size');
    }

    /**
     * Get the display name of the size.
     * e.g. "30 x 20 cm"
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->width} x {$this->height} {$this->unit}";
    }
}
