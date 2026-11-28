<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInventoryStockRequest;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $summary = ProductVariant::query()
            ->selectRaw('COALESCE(SUM(stock), 0) as total_units')
            ->selectRaw('COALESCE(SUM(CASE WHEN stock > 0 AND stock <= low_stock_threshold THEN 1 ELSE 0 END), 0) as low_stock_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN stock = 0 THEN 1 ELSE 0 END), 0) as out_of_stock_count')
            ->first();

        $query = ProductVariant::query()
            ->with('product:id,category_id,name,sku,image')
            ->whereHas('product')
            ->latest('id');

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());
            $query->whereHas('product', function (Builder $productQuery) use ($search): void {
                $productQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $status = $request->string('status')->toString();

        match ($status) {
            'aman' => $query->whereColumn('stock', '>', 'low_stock_threshold'),
            'menipis' => $query->lowStock(),
            'habis' => $query->where('stock', 0),
            default => null,
        };

        $variants = $query->paginate(15)->withQueryString();

        return view('admin.inventory.index', compact('summary', 'variants', 'status'));
    }

    public function updateStock(UpdateInventoryStockRequest $request, ProductVariant $productVariant): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($productVariant, $validated): void {
            $variant = ProductVariant::query()
                ->whereKey($productVariant->id)
                ->lockForUpdate()
                ->firstOrFail();

            $quantity = (int) $validated['quantity'];
            $currentStock = (int) $variant->stock;

            $newStock = match ($validated['operation']) {
                'set' => $quantity,
                'add' => $currentStock + $quantity,
                'subtract' => $currentStock - $quantity,
            };

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah pengurangan melebihi stok yang tersedia.',
                ]);
            }

            if ($newStock > 4294967295) {
                throw ValidationException::withMessages([
                    'quantity' => 'Jumlah stok melampaui batas maksimum.',
                ]);
            }

            $variant->update([
                'stock' => $newStock,
                'low_stock_threshold' => $validated['low_stock_threshold'],
            ]);
        });

        return back()->with('success', 'Stok varian berhasil diperbarui.');
    }
}
