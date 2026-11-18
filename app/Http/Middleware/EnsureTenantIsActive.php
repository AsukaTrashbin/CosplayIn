<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->user()?->tenant;

        if (! $tenant) {
            abort(403, 'Akun ini tidak terhubung dengan data tenant.');
        }

        if (! $tenant->isActive()) {
            if ($request->expectsJson()) {
                abort(403, 'Akun tenant belum aktif.');
            }

            return redirect()->route('home')->with(
                'error',
                'Akun tenant Anda masih berstatus "'.$tenant->statusLabel().'". Hubungi admin untuk aktivasi.'
            );
        }

        return $next($request);
    }
}
