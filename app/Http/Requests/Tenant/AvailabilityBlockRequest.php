<?php

namespace App\Http\Requests\Tenant;

use App\Models\Costume;
use Illuminate\Foundation\Http\FormRequest;

class AvailabilityBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isTenant() && $this->user()->tenant !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ukuran' => ['required', 'in:'.implode(',', Costume::SIZES)],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'catatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal.after_or_equal' => 'Tanggal blokir tidak boleh di masa lalu.',
        ];
    }
}
