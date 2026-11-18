<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
        $tenant = $this->input('role') === 'tenant';

        return [
            'role' => ['required', 'in:customer,tenant'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:30', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'store_name' => [$tenant ? 'required' : 'nullable', 'string', 'max:255'],
            'city' => [$tenant ? 'required' : 'nullable', 'string', 'max:100'],
            'address' => [$tenant ? 'required' : 'nullable', 'string', 'max:500'],
            'legal_name' => [$tenant ? 'required' : 'nullable', 'string', 'max:255'],
            'legal_id_number' => [$tenant ? 'required' : 'nullable', 'string', 'max:30'],
            'phone' => [$tenant ? 'required' : 'nullable', 'string', 'max:30'],
            'terms_accepted' => ['accepted'],
            'privacy_accepted' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'role.in' => 'Pilih peran customer atau tenant.',
            'terms_accepted.accepted' => 'Anda wajib menyetujui Syarat dan Ketentuan.',
            'privacy_accepted.accepted' => 'Anda wajib menyetujui Kebijakan Privasi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal 8 karakter.',
        ];
    }
}
