<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación para crear y actualizar productos.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'franchise' => ['required', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'category_id' => 'categoría',
            'name' => 'nombre',
            'description' => 'descripción',
            'price' => 'precio',
            'stock' => 'stock',
            'franchise' => 'franquicia',
        ];
    }
}
