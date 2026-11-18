<?php

namespace App\Http\Requests;

use App\Models\Costume;
use Illuminate\Foundation\Http\FormRequest;

class CatalogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
            'ukuran' => ['nullable', 'in:'.implode(',', Costume::SIZES)],
            'gender' => ['nullable', 'in:male,female,unisex'],
            'kategori' => ['nullable', 'integer', 'exists:categories,id'],
        ];
    }
}
