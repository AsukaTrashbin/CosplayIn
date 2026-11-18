<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'status' => 'active',
            ]);

            if ($data['role'] === 'tenant') {
                Tenant::query()->create([
                    'user_id' => $user->id,
                    'store_name' => $data['store_name'],
                    'city' => $data['city'],
                    'address' => $data['address'],
                    'legal_name' => $data['legal_name'],
                    'legal_id_number' => $data['legal_id_number'],
                    'phone' => $data['phone'],
                    'status' => Tenant::STATUS_PENDING,
                ]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        $message = 'Pendaftaran berhasil. Silakan verifikasi email Anda.';
        if ($user->isTenant()) {
            $message .= ' Akun tenant menunggu persetujuan admin sebelum dapat digunakan.';
        }

        return redirect()->route('verification.notice')->with('success', $message);
    }
}
