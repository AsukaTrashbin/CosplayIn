<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\CategoryRequest;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = $request->user()->tenant->id;

        $categories = Category::query()
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->withCount('costumes')
            ->orderBy('name')
            ->paginate(20);

        return view('tenant.kategori.index', compact('categories'));
    }

    public function create(): View
    {
        return view('tenant.kategori.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::query()->create([
            'tenant_id' => $request->user()->tenant->id,
            'name' => $request->validated()['name'],
        ]);

        return redirect()->route('tenant.categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        $this->authorizeOwnership($category);

        return view('tenant.kategori.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeOwnership($category);

        $category->update(['name' => $request->validated()['name']]);

        return redirect()->route('tenant.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorizeOwnership($category);

        $category->delete();

        return redirect()->route('tenant.categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function authorizeOwnership(Category $category): void
    {
        abort_unless(
            $category->tenant_id !== null && $category->tenant_id === auth()->user()->tenant->id,
            403,
            'Kategori global tidak dapat diubah oleh tenant.'
        );
    }
}
