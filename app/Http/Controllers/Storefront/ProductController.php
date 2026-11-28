<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::query()
            ->where('is_active', true)
            ->with([
                'category:id,name,slug',
                'variants' => function (HasMany $variantsQuery): void {
                    $variantsQuery->select('id', 'product_id', 'color', 'size', 'stock')
                        ->orderBy('id');
                },
            ])
            ->withSum('variants', 'stock');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->where(function (Builder $productQuery) use ($search): void {
                $productQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $selectedCategoryId = null;

        if ($request->filled('category_id')) {
            $selectedCategoryId = $request->integer('category_id');
            $query->where('category_id', $selectedCategoryId);
        } elseif ($request->filled('category')) {
            $categorySlug = $request->string('category')->toString();
            $query->whereHas('category', fn (Builder $categoryQuery): Builder => $categoryQuery->where('slug', $categorySlug));
        }

        $sort = $request->query('sort');
        $sort = in_array($sort, ['price_asc', 'price_desc'], true) ? $sort : 'latest';

        match ($sort) {
            'price_asc' => $query->orderBy('price')->latest('id'),
            'price_desc' => $query->orderByDesc('price')->latest('id'),
            default => $query->latest('id'),
        };

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::query()
            ->where('is_active', true)
            ->whereHas('products', fn (Builder $productQuery): Builder => $productQuery->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        if ($selectedCategoryId === null && $request->filled('category')) {
            $selectedCategoryId = $categories->firstWhere('slug', $request->string('category')->toString())?->id;
        }

        return view('storefront.products.index', compact('products', 'categories', 'sort', 'selectedCategoryId'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load([
            'category:id,name,slug',
            'variants' => function (HasMany $variantsQuery): void {
                $variantsQuery->select('id', 'product_id', 'color', 'size', 'stock', 'low_stock_threshold')
                    ->orderBy('id');
            },
        ]);

        $relatedProducts = Product::query()
            ->where('is_active', true)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with([
                'category:id,name,slug',
                'variants' => function (HasMany $variantsQuery): void {
                    $variantsQuery->select('id', 'product_id', 'color', 'size', 'stock')
                        ->orderBy('id');
                },
            ])
            ->withSum('variants', 'stock')
            ->latest('id')
            ->limit(4)
            ->get();

        $variantOptions = $product->variants
            ->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'color' => $variant->color,
                'size' => $variant->size,
                'stock' => $variant->stock,
                'low_stock_threshold' => $variant->low_stock_threshold,
            ])
            ->values()
            ->all();

        return view('storefront.products.show', compact('product', 'relatedProducts', 'variantOptions'));
    }
}
