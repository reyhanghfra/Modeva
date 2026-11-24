<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori produk.
     */
    public function index(Request $request): View
    {
        $query = Category::query();

        // Pencarian nama kategori
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter status jika ada
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('is_active', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->latest('id')->paginate(10)->withQueryString();
        $totalCategories = Category::count();

        return view('admin.categories.index', compact('categories', 'totalCategories'));
    }

    /**
     * Menampilkan formulir penambahan kategori baru.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Menyimpan kategori baru ke basis data.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan formulir pengeditan kategori.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Memperbarui data kategori yang ada.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Menghapus kategori dari sistem.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Periksa apakah kategori terhubung ke produk (aman saat tabel produk belum ada)
        if (! $category->canBeDeleted()) {
            return back()->with('error', 'Kategori sedang digunakan oleh produk, nonaktifkan saja.');
        }

        try {
            $category->delete();
            return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Kategori berhasil dihapus.');
        } catch (QueryException $e) {
            return back()->with('error', 'Kategori sedang digunakan oleh data produk terkait, nonaktifkan saja.');
        }
    }
}
