<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Costume;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;

class PublicTenantController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        $filters = $request->validated();

        $tenants = Tenant::query()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->with(['user', 'costumes' => fn ($q) => $q->visibleToPublic()->with('images')])
            ->withCount(['costumes' => fn ($q) => $q->where('is_active', true)])
            ->when($filters['q'] ?? null, function ($query, string $keyword) {
                $query->where(function ($inner) use ($keyword) {
                    $inner->where('store_name', 'like', '%'.$keyword.'%')
                        ->orWhere('city', 'like', '%'.$keyword.'%')
                        ->orWhere('address', 'like', '%'.$keyword.'%');
                });
            })
            ->when($filters['kota'] ?? null, fn ($q, string $city) => $q->where('city', $city))
            ->when($filters['ukuran'] ?? null, function ($query, string $size) {
                $query->whereHas('costumes', function ($q) use ($size) {
                    $q->visibleToPublic()->whereHas('sizes', fn ($s) => $s->where('size', $size)->where('stock', '>', 0));
                });
            })
            ->when($filters['gender'] ?? null, function ($query, string $gender) {
                $query->whereHas('costumes', function ($q) use ($gender) {
                    $q->visibleToPublic()->where(function ($g) use ($gender) {
                        $g->where('gender', $gender)->orWhere('gender', 'unisex');
                    });
                });
            })
            ->orderBy('store_name')
            ->paginate(12)
            ->withQueryString();

        $cities = Tenant::query()
            ->where('status', Tenant::STATUS_ACTIVE)
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        $categories = \App\Models\Category::query()->whereNull('tenant_id')->orderBy('name')->get();

        return view('tenants.index', compact('tenants', 'filters', 'cities', 'categories'));
    }

    public function show(Tenant $tenant, CatalogFilterRequest $request): View
    {
        abort_unless($tenant->status === Tenant::STATUS_ACTIVE, 404);

        $filters = $request->validated();

        $costumes = Costume::query()
            ->where('tenant_id', $tenant->id)
            ->visibleToPublic()
            ->with(['category', 'sizes', 'images'])
            ->filter($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $averageRating = $tenant->costumes()
            ->withAvg('reviews', 'rating')
            ->get()
            ->avg('reviews_avg_rating');

        return view('tenants.show', [
            'tenant' => $tenant->load('user'),
            'costumes' => $costumes,
            'filters' => $filters,
            'averageRating' => $averageRating ? round((float) $averageRating, 1) : 0,
        ]);
    }
}
