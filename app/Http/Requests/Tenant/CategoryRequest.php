<?php

namespace App\Http\Requests\Tenant;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->isTenant() || ! $user->tenant) {
            return false;
        }

        $category = $this->route('category');

        if ($category instanceof Category) {
            return $category->tenant_id === $user->tenant->id;
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }
}
