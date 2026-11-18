<?php

namespace App\Http\Requests;

use App\Models\Costume;
use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
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
            'costume_id' => ['required', 'integer', 'exists:costumes,id'],
            'nama_penyewa' => ['required', 'string', 'max:255'],
            'nomor_identitas' => ['required', 'digits_between:8,20'],
            'telepon' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'alamat' => ['required', 'string', 'max:500'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'ukuran' => ['required', 'in:'.implode(',', Costume::SIZES)],
            'metode_pengiriman' => ['required', 'in:courier,cod'],
            'ktp' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_selesai.after' => 'Tanggal kembali harus setelah tanggal mulai sewa.',
            'nomor_identitas.digits_between' => 'Nomor identitas harus berupa 8 sampai 20 digit angka.',
            'telepon.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda + atau -.',
            'ktp.mimes' => 'Foto KTP harus berformat JPG, JPEG, PNG, atau PDF.',
            'ktp.max' => 'Ukuran foto KTP maksimal 2 MB.',
        ];
    }
}
