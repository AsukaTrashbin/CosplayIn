<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TenantFilterRequest;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index(TenantFilterRequest $request): View
    {
        $filters = $request->validated();

        $tenants = Tenant::query()
            ->with('user')
            ->withCount(['costumes', 'bookings'])
            ->when($filters['status'] ?? null, fn ($q, string $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, string $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('store_name', 'like', '%'.$keyword.'%')
                        ->orWhere('city', 'like', '%'.$keyword.'%')
                        ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$keyword.'%'));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tenant.index', compact('tenants', 'filters'));
    }

    public function approve(Tenant $tenant, NotificationService $notifications): RedirectResponse
    {
        DB::transaction(function () use ($tenant) {
            $tenant->update([
                'status' => Tenant::STATUS_ACTIVE,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            $tenant->user->update(['status' => 'active']);
        });

        $notifications->send(
            $tenant->user,
            'Pendaftaran Tenant Disetujui',
            'Selamat! Akun tenant '.$tenant->store_name.' telah disetujui. Anda dapat mulai mengelola kostum.',
            route('tenant.dashboard')
        );

        return back()->with('success', 'Tenant '.$tenant->store_name.' berhasil diaktifkan.');
    }

    public function deactivate(Tenant $tenant, NotificationService $notifications): RedirectResponse
    {
        $tenant->update(['status' => Tenant::STATUS_INACTIVE]);

        $notifications->send(
            $tenant->user,
            'Akun Tenant Dinonaktifkan',
            'Akun tenant '.$tenant->store_name.' telah dinonaktifkan oleh admin.',
            route('home')
        );

        return back()->with('success', 'Tenant '.$tenant->store_name.' dinonaktifkan.');
    }

    public function activate(Tenant $tenant, NotificationService $notifications): RedirectResponse
    {
        $tenant->update([
            'status' => Tenant::STATUS_ACTIVE,
            'approved_at' => $tenant->approved_at ?? now(),
        ]);

        $notifications->send(
            $tenant->user,
            'Akun Tenant Diaktifkan Kembali',
            'Akun tenant '.$tenant->store_name.' telah diaktifkan kembali.',
            route('tenant.dashboard')
        );

        return back()->with('success', 'Tenant '.$tenant->store_name.' diaktifkan kembali.');
    }
}
