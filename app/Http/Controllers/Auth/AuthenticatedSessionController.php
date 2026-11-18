<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $request->session()->put('login_at', now()->toDateTimeString());

        $redirect = redirect()->intended(route($user->dashboardRoute()));

        $message = 'Selamat datang, '.$user->name.'.';
        if ($user->isTenant() && $user->tenant && ! $user->tenant->isActive()) {
            $message .= ' Status tenant Anda: '.$user->tenant->statusLabel().'.';
        }

        return $redirect->with('success', $message);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda telah logout.');
    }
}
