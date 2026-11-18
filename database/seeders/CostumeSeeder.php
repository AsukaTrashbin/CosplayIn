<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Costume;
use App\Models\CostumeSize;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CostumeSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()
            ->where('store_name', 'Cosplay Kingdom')
            ->firstOrFail();

        $categories = Category::query()->whereNull('tenant_id')->get()->keyBy('name');

        $costumes = [
            [
                'character_name' => 'Naruto Uzumaki',
                'category' => 'Anime',
                'gender' => 'male',
                'city' => 'Jakarta',
                'price_per_day' => 150000,
                'is_popular' => true,
                'description' => 'Kostum Naruto dewasa dengan bahan polyester premium. Termasuk jaket, celana, ikat kepala, dan pelindung kaki.',
                'accessories' => 'Wig pirang, ikat kepala, kunai mainan, dan pelindung kaki.',
                'stock' => ['S' => 2, 'M' => 3, 'L' => 2, 'XL' => 1],
            ],
            [
                'character_name' => 'Sailor Moon',
                'category' => 'Anime',
                'gender' => 'female',
                'city' => 'Bandung',
                'price_per_day' => 175000,
                'is_popular' => true,
                'description' => 'Kostum Sailor Moon lengkap dengan rok berlapis dan aksesori mahkota. Bahan satin dan tulle.',
                'accessories' => 'Wig pirang, mahkota, sepatu bot, dan tongkat bulan.',
                'stock' => ['S' => 2, 'M' => 2, 'L' => 1, 'XL' => 1],
            ],
            [
                'character_name' => 'Raiden Shogun',
                'category' => 'Game',
                'gender' => 'female',
                'city' => 'Jakarta',
                'price_per_day' => 250000,
                'is_popular' => true,
                'description' => 'Kostum Genshin Impact Raiden Shogun dengan detail bordir dan kain tebal. Termasuk kimono dan aksesori pinggang.',
                'accessories' => 'Wig ungu, katana mainan, dan aksesori elektro.',
                'stock' => ['S' => 1, 'M' => 2, 'L' => 2, 'XL' => 0],
            ],
            [
                'character_name' => 'Zhongli',
                'category' => 'Game',
                'gender' => 'male',
                'city' => 'Surabaya',
                'price_per_day' => 225000,
                'is_popular' => false,
                'description' => 'Kostum Zhongli dengan jas panjang dan detail ornamen emas. Bahan wol campuran.',
                'accessories' => 'Wig cokelat, ornamen emas, dan tombak mainan.',
                'stock' => ['S' => 1, 'M' => 2, 'L' => 2, 'XL' => 1],
            ],
            [
                'character_name' => 'Spider-Man',
                'category' => 'Film',
                'gender' => 'male',
                'city' => 'Bandung',
                'price_per_day' => 200000,
                'is_popular' => false,
                'description' => 'Kostum Spider-Man berbahan spandex elastis. Nyaman untuk foto dan cosplay harian.',
                'accessories' => 'Masker full face dan web shooter mainan.',
                'stock' => ['S' => 1, 'M' => 2, 'L' => 2, 'XL' => 1],
            ],
            [
                'character_name' => 'Elsa Frozen',
                'category' => 'Film',
                'gender' => 'female',
                'city' => 'Yogyakarta',
                'price_per_day' => 185000,
                'is_popular' => false,
                'description' => 'Kostum Elsa dengan gaun biru berkilau dan cape panjang. Cocok untuk acara anak dan cosplay.',
                'accessories' => 'Wig platinum, mahkota, dan gaun cape.',
                'stock' => ['S' => 2, 'M' => 2, 'L' => 1, 'XL' => 0],
            ],
            [
                'character_name' => 'Hatsune Miku',
                'category' => 'Anime',
                'gender' => 'female',
                'city' => 'Medan',
                'price_per_day' => 165000,
                'is_popular' => false,
                'description' => 'Kostum Hatsune Miku dengan rok plisket dan dasi. Bahan polyester ringan.',
                'accessories' => 'Wig twintail biru, headset, dan mikrofon mainan.',
                'stock' => ['S' => 1, 'M' => 2, 'L' => 1, 'XL' => 1],
            ],
            [
                'character_name' => 'Kamen Rider',
                'category' => 'Tokoh Umum',
                'gender' => 'unisex',
                'city' => 'Semarang',
                'price_per_day' => 145000,
                'is_popular' => false,
                'description' => 'Kostum Kamen Rider dengan armor EVA foam ringan. Termasuk sabuk transformasi.',
                'accessories' => 'Helm, sabuk, dan sarung tangan.',
                'stock' => ['S' => 1, 'M' => 2, 'L' => 2, 'XL' => 1],
            ],
        ];

        foreach ($costumes as $data) {
            $costume = Costume::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'character_name' => $data['character_name'],
                ],
                [
                    'category_id' => $categories->get($data['category'])?->id,
                    'description' => $data['description'],
                    'accessories' => $data['accessories'],
                    'gender' => $data['gender'],
                    'city' => $data['city'],
                    'price_per_day' => $data['price_per_day'],
                    'deposit' => 100000,
                    'is_popular' => $data['is_popular'],
                    'is_active' => true,
                ]
            );

            foreach ($data['stock'] as $size => $stock) {
                CostumeSize::query()->updateOrCreate(
                    ['costume_id' => $costume->id, 'size' => $size],
                    ['stock' => $stock]
                );
            }

            $costume->images()->firstOrCreate(['path' => 'images/placeholder.svg']);
        }
    }
}
