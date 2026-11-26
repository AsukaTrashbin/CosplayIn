<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogFilterRequest;
use App\Models\Costume;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(CatalogFilterRequest $request): View
    {
        $filters = $request->validated();

        /**
         * Data contoh untuk kartu "Cosplay populer".
         * Ganti dengan query database saat data kostum sudah siap.
         *
         * @var array<int, array{name: string, price: int, available: bool}>
         */
        $popularCosplays = [
            ['name' => 'Nama Karakter', 'price' => 150000, 'available' => true],
            ['name' => 'Nama Karakter', 'price' => 120000, 'available' => true],
            ['name' => 'Nama Karakter', 'price' => 135000, 'available' => true],
            ['name' => 'Nama Karakter', 'price' => 100000, 'available' => true],
        ];

        $cities = Costume::query()
            ->visibleToPublic()
            ->select('city')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('home', compact('filters', 'popularCosplays', 'cities'));
    }
}
