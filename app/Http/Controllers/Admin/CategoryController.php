<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories with article counts.
     */
    public function index(): View
    {
        $categories = Category::withCount('articles')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(StoreCategoryRequest $request, SlugGenerator $slugGen): RedirectResponse
    {
        $validated = $request->validated();
        $slug = $slugGen->generate((string) $validated['name'], modelClass: Category::class);

        Category::create([
            'name' => (string) $validated['name'],
            'slug' => $slug,
            'description' => isset($validated['description']) ? (string) $validated['description'] : null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category, SlugGenerator $slugGen): RedirectResponse
    {
        $validated = $request->validated();
        $slug = $slugGen->generate((string) $validated['name'], ignoreId: $category->id, modelClass: Category::class);

        $category->update([
            'name' => (string) $validated['name'],
            'slug' => $slug,
            'description' => isset($validated['description']) ? (string) $validated['description'] : null,
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Remove the specified category from storage.
     * Prevents deletion if the category has associated articles.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $articlesCount = $category->articles()->withTrashed()->count();

        if ($articlesCount > 0) {
            return redirect()->back()->with('error', "Kategori '{$category->name}' tidak dapat dihapus karena masih digunakan oleh {$articlesCount} artikel. Pindahkan artikel ke kategori lain terlebih dahulu.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "Kategori '{$category->name}' berhasil dihapus.");
    }
}
