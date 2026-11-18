<?php

namespace App\Http\Requests;

use App\Models\Costume;
use Illuminate\Foundation\Http\FormRequest;

class AvailabilityCheckRequest extends FormRequest
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
            'ukuran' => ['required', 'in:'.implode(',', Costume::SIZES)],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_selesai.after' => 'Tanggal kembali harus setelah tanggal mulai sewa.',
        ];
    }
}
