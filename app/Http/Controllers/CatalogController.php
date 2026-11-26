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
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->filter($filters)
            ->sortCatalog($filters['urutkan'] ?? 'paling_sesuai')
            ->paginate(8)
            ->withQueryString();

        $trending = Costume::query()
            ->visibleToPublic()
            ->with(['tenant', 'category', 'images'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->take(5)
            ->get();

        $categories = Category::query()
            ->whereNull('tenant_id')
            ->orderBy('name')
            ->get();

        $cities = collect(Costume::JABODETABEK_CITIES);
        $catalogCategories = Costume::CATALOG_CATEGORIES;
        $sortOptions = Costume::SORT_OPTIONS;

        $selectedCategories = $this->resolveSelectedCategorySlugs($filters['kategori'] ?? [], $categories);

        return view('katalog.index', compact(
            'filters',
            'costumes',
            'trending',
            'categories',
            'cities',
            'catalogCategories',
            'sortOptions',
            'selectedCategories'
        ));
    }

    /**
     * Normalisasi parameter kategori (slug maupun ID numerik) ke slug checkbox katalog.
     *
     * @param  array<int, string>  $rawCategories
     * @param  \Illuminate\Support\Collection<int, Category>  $categories
     * @return array<int, string>
     */
    private function resolveSelectedCategorySlugs(array $rawCategories, \Illuminate\Support\Collection $categories): array
    {
        $byId = $categories->keyBy('id');
        $slugs = [];

        foreach ($rawCategories as $item) {
            if (array_key_exists($item, Costume::CATALOG_CATEGORIES)) {
                $slugs[] = $item;
                continue;
            }

            if (is_numeric($item) && $byId->has((int) $item)) {
                $name = strtolower($byId->get((int) $item)->name);
                if (str_contains($name, 'anime')) {
                    $slugs[] = 'anime';
                } elseif (str_contains($name, 'game')) {
                    $slugs[] = 'game';
                } elseif (str_contains($name, 'film') || str_contains($name, 'serial')) {
                    $slugs[] = 'film-serial';
                } elseif (str_contains($name, 'original') || str_contains($name, 'tokoh umum')) {
                    $slugs[] = 'original';
                }
            }
        }

        return array_values(array_unique($slugs));
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
