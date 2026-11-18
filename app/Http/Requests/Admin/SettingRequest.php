<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'late_fee_per_day' => ['required', 'integer', 'min:0', 'max:10000000'],
            'max_late_fee' => ['required', 'integer', 'min:0', 'max:100000000'],
            'default_deposit' => ['required', 'integer', 'min:0', 'max:100000000'],
            'shipping_fee' => ['required', 'integer', 'min:0', 'max:10000000'],
            'min_rental_days' => ['required', 'integer', 'min:1', 'max:30'],
            'max_rental_days' => ['required', 'integer', 'min:1', 'max:90', 'gte:min_rental_days'],
            'platform_commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'max_rental_days.gte' => 'Maksimal durasi sewa harus lebih besar atau sama dengan minimal durasi sewa.',
        ];
    }
}
