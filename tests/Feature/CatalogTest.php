<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesCosplayData;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use CreatesCosplayData, RefreshDatabase;

    public function test_catalog_page_renders_required_filters_and_sort_options(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $this->createCostume($tenant, ['character_name' => 'Gojo Satoru']);

        $response = $this->get(route('catalog.index'));

        $response->assertOk()
            ->assertSee('Karakter baru, cerita baru.')
            ->assertSee('Gojo Satoru')
            ->assertSee('Semua kota')
            ->assertSee('Film & serial')
            ->assertSee('Harga Minimum')
            ->assertSee('Harga Maksimum')
            ->assertSee('Paling sesuai')
            ->assertSee('Ulasan')
            ->assertSee('Terbaru')
            ->assertSee('Harga tertinggi')
            ->assertSee('Harga terendah');
    }

    public function test_catalog_filters_by_jabodetabek_city_category_checkbox_and_price_range(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();

        $anime = Category::query()->firstOrCreate(['tenant_id' => null, 'name' => 'Anime']);
        $game = Category::query()->firstOrCreate(['tenant_id' => null, 'name' => 'Game']);
        $film = Category::query()->firstOrCreate(['tenant_id' => null, 'name' => 'Film & serial']);

        $match = $this->createCostume($tenant, [
            'character_name' => 'Marin Kitagawa',
            'category_id' => $anime->id,
            'city' => 'Jakarta Selatan',
            'gender' => 'female',
            'price_per_day' => 135000,
        ]);

        $this->createCostume($tenant, [
            'character_name' => 'Raiden Shogun',
            'category_id' => $game->id,
            'city' => 'Depok',
            'gender' => 'female',
            'price_per_day' => 260000,
        ]);

        $this->createCostume($tenant, [
            'character_name' => 'Spider-Man',
            'category_id' => $film->id,
            'city' => 'Bekasi',
            'gender' => 'male',
            'price_per_day' => 120000,
        ]);

        $response = $this->get(route('catalog.index', [
            'kota' => 'Jakarta',
            'ukuran' => 'M',
            'gender' => 'female',
            'kategori' => ['anime', 'game'],
            'harga_min' => '100000',
            'harga_max' => '200000',
        ]));

        $response->assertOk()
            ->assertSee($match->character_name)
            ->assertDontSee('Raiden Shogun')
            ->assertDontSee('Spider-Man');
    }

    public function test_catalog_sorts_by_price_and_reviews(): void
    {
        [$tenantUser, $tenant] = $this->createTenantUser();
        $customer = $this->createCustomer();

        $cheap = $this->createCostume($tenant, [
            'character_name' => 'Kostum Murah',
            'price_per_day' => 90000,
        ]);

        $expensive = $this->createCostume($tenant, [
            'character_name' => 'Kostum Mahal',
            'price_per_day' => 300000,
        ]);

        $booking = $this->createBooking($customer, $tenant, $cheap);
        Review::query()->create([
            'booking_id' => $booking->id,
            'reviewer_id' => $customer->id,
            'target' => Review::TARGET_TENANT,
            'rating' => 5,
            'comment' => 'Sangat bagus',
        ]);

        $this->get(route('catalog.index', ['urutkan' => 'harga_tertinggi']))
            ->assertOk()
            ->assertSeeInOrder(['Kostum Mahal', 'Kostum Murah']);

        $this->get(route('catalog.index', ['urutkan' => 'harga_terendah']))
            ->assertOk()
            ->assertSeeInOrder(['Kostum Murah', 'Kostum Mahal']);

        $this->get(route('catalog.index', ['urutkan' => 'ulasan']))
            ->assertOk()
            ->assertSeeInOrder(['Kostum Murah', 'Kostum Mahal']);
    }
}
