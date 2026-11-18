<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserFilterRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(UserFilterRequest $request): View
    {
        $filters = $request->validated();

        $users = User::query()
            ->with('tenant')
            ->when($filters['role'] ?? null, fn ($q, string $role) => $q->where('role', $role))
            ->when($filters['status'] ?? null, fn ($q, string $status) => $q->where('status', $status))
            ->when($filters['q'] ?? null, function ($query, string $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('name', 'like', '%'.$keyword.'%')
                        ->orWhere('email', 'like', '%'.$keyword.'%')
                        ->orWhere('username', 'like', '%'.$keyword.'%');
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function toggle(Request $request, User $user, NotificationService $notifications): RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 422, 'Anda tidak dapat menonaktifkan akun sendiri.');

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';

        $user->update(['status' => $newStatus]);

        if ($user->isTenant() && $user->tenant) {
            $user->tenant->update([
                'status' => $newStatus === 'active' ? 'active' : 'inactive',
            ]);
        }

        $notifications->send(
            $user,
            $newStatus === 'active' ? 'Akun Diaktifkan' : 'Akun Dinonaktifkan',
            $newStatus === 'active'
                ? 'Akun Anda telah diaktifkan kembali oleh admin.'
                : 'Akun Anda telah dinonaktifkan oleh admin. Hubungi admin untuk informasi lebih lanjut.',
            route('home')
        );

        return back()->with('success', 'Status pengguna '.$user->name.' diubah menjadi '.$newStatus.'.');
    }
}
