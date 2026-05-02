<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'name', 
    'hex_code', 
    'is_active', 
    'featured_order',
])]
class Color extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'featured_order' => 'integer',
        ];
    }
}
