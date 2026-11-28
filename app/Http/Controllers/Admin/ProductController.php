<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->with('category')
            ->withTotalStock()
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        $products = $query->paginate(12)->withQueryString();
        $totalProducts = Product::query()->count();
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'totalProducts', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::query()->orderBy('name')->get();
        $product = new Product;
        $variantRows = old('variants', [[]]);
        $variantRows = is_array($variantRows) ? array_values(array_filter($variantRows, 'is_array')) : [];
        $variantRows = $variantRows === [] ? [[]] : $variantRows;

        return view('admin.products.create', compact('categories', 'product', 'variantRows'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $variants = $validated['variants'];
        unset($validated['variants']);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                return back()->withInput()->withErrors(['image' => 'Foto produk gagal disimpan. Silakan coba kembali.']);
            }

            $validated['image'] = $imagePath;
        }

        DB::transaction(function () use ($validated, $variants): void {
            $product = Product::query()->create($validated);
            $product->variants()->createMany($variants);
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk baru berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $product->load('variants');
        $categories = Category::query()->orderBy('name')->get();
        $variantRows = old('variants', $product->variants->toArray());
        $variantRows = is_array($variantRows) ? array_values(array_filter($variantRows, 'is_array')) : [];
        $variantRows = $variantRows === [] ? [[]] : $variantRows;

        return view('admin.products.edit', compact('product', 'categories', 'variantRows'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $variants = $validated['variants'];
        unset($validated['variants']);
        $previousImage = $product->image;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');

            if ($imagePath === false) {
                return back()->withInput()->withErrors(['image' => 'Foto produk gagal disimpan. Silakan coba kembali.']);
            }

            $validated['image'] = $imagePath;
        }

        DB::transaction(function () use ($product, $validated, $variants): void {
            $product->update($validated);
            $retainedVariantIds = collect($variants)
                ->pluck('id')
                ->filter()
                ->all();

            $product->variants()
                ->whereNotIn('id', $retainedVariantIds)
                ->delete();

            foreach ($variants as $variantData) {
                $variantId = $variantData['id'] ?? null;
                unset($variantData['id']);

                if ($variantId !== null) {
                    $product->variants()->whereKey($variantId)->update($variantData);
                    $retainedVariantIds[] = $variantId;
                } else {
                    $createdVariant = $product->variants()->create($variantData);
                    $retainedVariantIds[] = $createdVariant->id;
                }
            }
        });

        if ($request->hasFile('image') && $previousImage !== null) {
            Storage::disk('public')->delete($previousImage);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $imagePath = $product->image;
        $product->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }
}
