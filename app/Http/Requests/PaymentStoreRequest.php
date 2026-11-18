<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isCustomer();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'metode' => ['required', 'in:transfer,ewallet,qris'],
        ];
    }

    public function messages(): array
    {
        return [
            'metode.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'metode.in' => 'Metode pembayaran tidak valid.',
        ];
    }
}
