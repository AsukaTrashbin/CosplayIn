<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRequest extends FormRequest
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
            'metode' => ['required', 'in:courier,cod'],
            'nomor_resi' => ['required_if:metode,courier', 'nullable', 'string', 'max:100'],
            'lokasi_cod' => ['required_if:metode,cod', 'nullable', 'string', 'max:255'],
            'waktu_cod' => ['required_if:metode,cod', 'nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor_resi.required_if' => 'Nomor resi wajib diisi untuk pengembalian via kurir.',
            'lokasi_cod.required_if' => 'Lokasi pertemuan wajib diisi untuk pengembalian COD.',
            'waktu_cod.required_if' => 'Waktu pertemuan wajib diisi untuk pengembalian COD.',
        ];
    }
}
