<?php

namespace App\Http\Requests;

use App\Models\Costume;
use Illuminate\Foundation\Http\FormRequest;

class CatalogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $hargaMin = $this->sanitizePrice($this->input('harga_min'));
        $hargaMax = $this->sanitizePrice($this->input('harga_max'));

        $kategori = $this->input('kategori');
        if (is_string($kategori) && $kategori !== '') {
            $kategori = [$kategori];
        } elseif (! is_array($kategori)) {
            $kategori = [];
        }

        $kategori = array_values(array_filter(array_map(
            static fn ($item) => is_scalar($item) ? trim((string) $item) : '',
            $kategori
        ), static fn (string $item) => $item !== ''));

        $this->merge([
            'harga_min' => $hargaMin,
            'harga_max' => $hargaMax,
            'kategori' => $kategori,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
            'ukuran' => ['nullable', 'in:'.implode(',', Costume::SIZES)],
            'gender' => ['nullable', 'in:male,female,unisex'],
            'kategori' => ['nullable', 'array'],
            'kategori.*' => ['string', 'max:50'],
            'harga_min' => ['nullable', 'integer', 'min:0'],
            'harga_max' => ['nullable', 'integer', 'min:0'],
            'tersedia' => ['nullable', 'boolean'],
            'urutkan' => ['nullable', 'in:'.implode(',', array_keys(Costume::SORT_OPTIONS))],
        ];
    }

    private function sanitizePrice(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        return $digits === '' ? null : $digits;
    }
}
