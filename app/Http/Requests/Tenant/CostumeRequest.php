<?php

namespace App\Http\Requests\Tenant;

use App\Models\Costume;
use Illuminate\Foundation\Http\FormRequest;

class CostumeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $costume = $this->route('costume');

        if ($costume instanceof Costume) {
            return $this->user()?->can('update', $costume) ?? false;
        }

        return $this->user()?->can('create', Costume::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'character_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'accessories' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'gender' => ['required', 'in:male,female,unisex'],
            'city' => ['required', 'string', 'max:100'],
            'price_per_day' => ['required', 'integer', 'min:1000', 'max:100000000'],
            'deposit' => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'is_popular' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'stock_s' => ['required', 'integer', 'min:0', 'max:999'],
            'stock_m' => ['required', 'integer', 'min:0', 'max:999'],
            'stock_l' => ['required', 'integer', 'min:0', 'max:999'],
            'stock_xl' => ['required', 'integer', 'min:0', 'max:999'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', 'exists:costume_images,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'character_name.required' => 'Nama karakter wajib diisi.',
            'description.required' => 'Deskripsi kostum wajib diisi.',
            'price_per_day.min' => 'Harga sewa minimal Rp1.000 per hari.',
            'stock_s.required' => 'Stok ukuran S wajib diisi.',
            'stock_m.required' => 'Stok ukuran M wajib diisi.',
            'stock_l.required' => 'Stok ukuran L wajib diisi.',
            'stock_xl.required' => 'Stok ukuran XL wajib diisi.',
            'images.max' => 'Maksimal 5 foto per unggahan.',
            'images.*.image' => 'Semua file harus berupa gambar.',
            'images.*.max' => 'Ukuran setiap gambar maksimal 2 MB.',
        ];
    }

    /**
     * @return array<string, int>
     */
    public function stockData(): array
    {
        return [
            'S' => (int) $this->input('stock_s', 0),
            'M' => (int) $this->input('stock_m', 0),
            'L' => (int) $this->input('stock_l', 0),
            'XL' => (int) $this->input('stock_xl', 0),
        ];
    }
}
