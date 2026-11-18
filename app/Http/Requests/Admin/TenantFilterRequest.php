<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class TenantFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:pending,active,inactive'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
