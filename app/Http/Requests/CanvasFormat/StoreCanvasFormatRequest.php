<?php

namespace App\Http\Requests\CanvasFormat;

use Illuminate\Foundation\Http\FormRequest;

class StoreCanvasFormatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:canvas_formats,slug'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'sizes' => ['nullable', 'array'],
            'sizes.*.id' => ['required', 'exists:canvas_sizes,id'],
            'sizes.*.sort_order' => ['nullable', 'integer'],
        ];
    }
}
