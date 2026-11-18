<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\TenantProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('tenant.profil', ['tenant' => $request->user()->tenant]);
    }

    public function update(TenantProfileRequest $request): RedirectResponse
    {
        $request->user()->tenant->update($request->validated());

        return redirect()->route('tenant.profile.edit')->with('success', 'Profil toko berhasil diperbarui.');
    }
}
