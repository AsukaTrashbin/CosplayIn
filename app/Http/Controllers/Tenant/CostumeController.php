<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CostumeRequest;
use App\Models\Category;
use App\Models\Costume;
use App\Models\CostumeSize;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CostumeController extends Controller
{
    public function index(Request $request): View
    {
        $costumes = $request->user()->tenant
            ->costumes()
            ->with(['category', 'sizes', 'images'])
            ->latest()
            ->paginate(10);

        return view('tenant.kostum.index', compact('costumes'));
    }

    public function create(): View
    {
        return view('tenant.kostum.create', [
            'categories' => $this->categories(),
            'costume' => new Costume(),
        ]);
    }

    public function store(CostumeRequest $request): RedirectResponse
    {
        $this->assertCategoryAllowed($request->input('category_id'));

        $data = $request->validated();

        $costume = DB::transaction(function () use ($request, $data) {
            $costume = Costume::query()->create([
                'tenant_id' => $request->user()->tenant->id,
                'category_id' => $data['category_id'] ?? null,
                'character_name' => $data['character_name'],
                'description' => $data['description'],
                'accessories' => $data['accessories'] ?? null,
                'gender' => $data['gender'],
                'city' => $data['city'],
                'price_per_day' => $data['price_per_day'],
                'deposit' => $data['deposit'] ?? 0,
                'is_popular' => $request->boolean('is_popular'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $this->syncSizes($costume, $request->stockData());
            $this->storeImages($costume, $request->file('images', []));

            return $costume;
        });

        return redirect()
            ->route('tenant.costumes.index')
            ->with('success', 'Kostum "'.$costume->character_name.'" berhasil ditambahkan.');
    }

    public function edit(Costume $costume): View
    {
        $this->authorize('update', $costume);

        $costume->load(['sizes', 'images']);

        return view('tenant.kostum.edit', [
            'costume' => $costume,
            'categories' => $this->categories(),
        ]);
    }

    public function update(CostumeRequest $request, Costume $costume): RedirectResponse
    {
        $this->authorize('update', $costume);
        $this->assertCategoryAllowed($request->input('category_id'));

        $data = $request->validated();

        DB::transaction(function () use ($request, $costume, $data) {
            $costume->update([
                'category_id' => $data['category_id'] ?? null,
                'character_name' => $data['character_name'],
                'description' => $data['description'],
                'accessories' => $data['accessories'] ?? null,
                'gender' => $data['gender'],
                'city' => $data['city'],
                'price_per_day' => $data['price_per_day'],
                'deposit' => $data['deposit'] ?? 0,
                'is_popular' => $request->boolean('is_popular'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $this->syncSizes($costume, $request->stockData());
            $this->removeImages($costume, $request->input('remove_images', []));
            $this->storeImages($costume, $request->file('images', []));
        });

        return redirect()
            ->route('tenant.costumes.index')
            ->with('success', 'Data kostum berhasil diperbarui.');
    }

    public function destroy(Costume $costume): RedirectResponse
    {
        $this->authorize('delete', $costume);

        $costume->delete();

        return redirect()
            ->route('tenant.costumes.index')
            ->with('success', 'Kostum berhasil dihapus (soft delete).');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Category>
     */
    private function categories()
    {
        return Category::query()
            ->where(function ($query) {
                $query->whereNull('tenant_id')
                    ->orWhere('tenant_id', auth()->user()->tenant->id);
            })
            ->orderBy('name')
            ->get();
    }

    private function assertCategoryAllowed(?string $categoryId): void
    {
        if (! $categoryId) {
            return;
        }

        $category = Category::query()->findOrFail($categoryId);
        $tenantId = auth()->user()->tenant->id;

        abort_unless($category->tenant_id === null || $category->tenant_id === $tenantId, 403);
    }

    /**
     * @param  array<string, int>  $stocks
     */
    private function syncSizes(Costume $costume, array $stocks): void
    {
        foreach ($stocks as $size => $stock) {
            CostumeSize::query()->updateOrCreate(
                ['costume_id' => $costume->id, 'size' => $size],
                ['stock' => $stock]
            );
        }
    }

    /**
     * @param  array<int, \Illuminate\Http\UploadedFile>  $files
     */
    private function storeImages(Costume $costume, array $files): void
    {
        foreach ($files as $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store('costumes', 'public');

            $costume->images()->create(['path' => $path]);
        }
    }

    /**
     * @param  array<int, int|string>  $ids
     */
    private function removeImages(Costume $costume, array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $images = $costume->images()->whereIn('id', $ids)->get();

        foreach ($images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }
}
