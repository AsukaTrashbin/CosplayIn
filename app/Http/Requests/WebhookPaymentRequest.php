<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WebhookPaymentRequest extends FormRequest
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
            'external_id' => ['required', 'string', 'max:100'],
            'status' => ['required', 'in:pending,success,failed,expired'],
            'amount' => ['nullable', 'integer', 'min:0'],
            'signature' => ['nullable', 'string', 'max:255'],
        ];
    }
}
