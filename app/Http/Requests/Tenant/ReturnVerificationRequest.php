<?php

namespace App\Http\Requests\Tenant;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;

class ReturnVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $booking = $this->route('booking');

        return $booking instanceof Booking
            && ($this->user()?->can('manage', $booking) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tanggal_kembali_aktual' => ['required', 'date', 'before_or_equal:today'],
            'kondisi' => ['required', 'in:good,damaged_lost'],
            'klaim_kerusakan' => ['required_if:kondisi,damaged_lost', 'nullable', 'integer', 'min:0', 'max:100000000'],
            'catatan_tenant' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_kembali_aktual.before_or_equal' => 'Tanggal kembali aktual tidak boleh di masa depan.',
            'klaim_kerusakan.required_if' => 'Nominal klaim kerusakan wajib diisi jika kostum rusak atau hilang.',
        ];
    }
}
