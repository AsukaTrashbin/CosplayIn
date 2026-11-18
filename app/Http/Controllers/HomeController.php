<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityCheckRequest;
use App\Http\Requests\CatalogFilterRequest;
use App\Models\Category;
use App\Models\Costume;
use App\Models\Review;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        $filters = $request->validated();

        $query = Costume::query()
            ->visibleToPublic()
            ->with(['tenant', 'category', 'sizes', 'images'])
            ->filter($filters);

        $costumes = (clone $query)->latest()->paginate(8)->withQueryString();

        $popular = Costume::query()
            ->visibleToPublic()
            ->with(['tenant', 'sizes', 'images'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->orderByDesc('is_popular')
            ->take(6)
            ->get();

        $categories = Category::query()
            ->whereNull('tenant_id')
            ->withCount(['costumes' => fn ($q) => $q->visibleToPublic()])
            ->orderBy('name')
            ->get();

        $cities = Costume::query()
            ->visibleToPublic()
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('home', compact('filters', 'costumes', 'popular', 'categories', 'cities'));
    }
}
