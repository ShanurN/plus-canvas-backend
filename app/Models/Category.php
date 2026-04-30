<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Support\Str;

#[Fillable([
    'name', 
    'slug', 
    'is_active', 
    'is_featured', 
    'featured_order', 
    'is_most_searched', 
    'most_searched_order'
])]
class Category extends Model
{
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        
        static::updating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'featured_order' => 'integer',
            'is_most_searched' => 'boolean',
            'most_searched_order' => 'integer',
        ];
    }

    /**
     * Scope for featured categories
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->orderBy('featured_order');
    }

    /**
     * Scope for most searched categories
     */
    public function scopeMostSearched($query)
    {
        return $query->where('is_most_searched', true)->orderBy('most_searched_order');
    }
}
