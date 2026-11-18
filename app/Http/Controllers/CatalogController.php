<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCheckRequest;
use App\Http\Requests\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Costume;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        $filters = $request->validated();

        $costumes = Costume::query()
            ->visibleToPublic()
            ->with(['tenant', 'category', 'sizes', 'images'])
            ->filter($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $trending = Costume::query()
            ->visibleToPublic()
            ->with(['tenant', 'images'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        $categories = Category::query()
            ->whereNull('tenant_id')
            ->orderBy('name')
            ->get();

        $cities = Costume::query()
            ->visibleToPublic()
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('katalog.index', compact('filters', 'costumes', 'trending', 'categories', 'cities'));
    }

    public function show(Costume $costume, Request $request): View
    {
        $costume->load(['tenant.user', 'category', 'images', 'sizes']);

        abort_unless($costume->is_active && $costume->tenant?->isActive(), 404);

        $reviews = $costume->reviews()
            ->with('reviewer')
            ->latest()
            ->paginate(5);

        $averageRating = $costume->averageRating();

        $availability = session('availability_result');

        return view('katalog.show', compact('costume', 'reviews', 'averageRating', 'availability'));
    }

    public function availability(AvailabilityCheckRequest $request, Costume $costume, AvailabilityService $availability): RedirectResponse
    {
        abort_unless($costume->is_active && $costume->tenant?->isActive(), 404);

        $data = $request->validated();
        $available = $availability->availableStock(
            $costume->id,
            $data['ukuran'],
            $data['tanggal_mulai'],
            $data['tanggal_selesai']
        );

        return redirect()
            ->route('catalog.show', $costume)
            ->with('availability_result', [
                'ukuran' => $data['ukuran'],
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'available' => $available,
                'available_stock' => $available,
            ]);
    }
}
