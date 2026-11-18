<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'role' => ['nullable', 'in:customer,tenant,admin'],
            'status' => ['nullable', 'in:active,inactive'],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
