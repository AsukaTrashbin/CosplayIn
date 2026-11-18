<?php

namespace App\Http\Requests\Admin;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class TransactionFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:'.implode(',', Booking::STATUSES)],
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
