<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\StoreCartItemRequest;
use App\Http\Requests\Pelanggan\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Support\Rupiah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja khusus pelanggan yang sedang login.
     */
    public function index(Request $request): View
    {
        $cartItems = CartItem::query()
            ->where('user_id', $request->user()->id)
            ->with(['variant.product.category'])
            ->latest('id')
            ->get();

        $totalItems = (int) $cartItems->sum('quantity');
        $subtotal = (int) $cartItems->sum(fn (CartItem $item): int => $item->subtotal);
        $total = $subtotal;

        $formattedSubtotal = Rupiah::format($subtotal);
        $formattedTotal = Rupiah::format($total);

        $hasStockIssue = $cartItems->contains(function (CartItem $item): bool {
            $variant = $item->variant;
            $product = $variant?->product;

            return ! $variant
                || ! $product
                || ! $product->is_active
                || $variant->stock < 1
                || $item->quantity > $variant->stock;
        });

        return view('pelanggan.cart.index', compact(
            'cartItems',
            'totalItems',
            'subtotal',
            'total',
            'formattedSubtotal',
            'formattedTotal',
            'hasStockIssue'
        ));
    }

    /**
     * Menambahkan varian produk ke keranjang pelanggan dengan validasi stok.
     */
    public function store(StoreCartItemRequest $request): RedirectResponse
    {
        $variant = ProductVariant::query()
            ->with('product')
            ->findOrFail($request->integer('product_variant_id'));

        if (! $variant->product || ! $variant->product->is_active) {
            return back()->with('error', 'Produk ini sedang tidak tersedia.');
        }

        if ($variant->stock < 1) {
            return back()->with('error', 'Stok varian produk ini sedang habis.');
        }

        $addedQuantity = max(1, $request->integer('quantity', 1));
        $userId = $request->user()->id;

        $existingItem = CartItem::query()
            ->where('user_id', $userId)
            ->where('product_variant_id', $variant->id)
            ->first();

        $newQuantity = ($existingItem?->quantity ?? 0) + $addedQuantity;

        if ($newQuantity > $variant->stock) {
            return back()->with(
                'error',
                "Jumlah melebihi stok varian yang tersedia (tersisa {$variant->stock})."
            );
        }

        if ($existingItem) {
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'user_id' => $userId,
                'product_variant_id' => $variant->id,
                'quantity' => $newQuantity,
            ]);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk berhasil ditambahkan ke keranjang belanja.');
    }

    /**
     * Memperbarui jumlah item di keranjang (tambah, kurangi, atau ubah jumlah) dengan validasi kepemilikan & stok.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah item keranjang ini.');
        }

        $cartItem->load('variant.product');
        $variant = $cartItem->variant;

        if (! $variant || ! $variant->product || ! $variant->product->is_active) {
            return back()->with('error', 'Produk atau varian ini sudah tidak tersedia.');
        }

        $action = $request->input('action');
        $newQuantity = match ($action) {
            'increase' => $cartItem->quantity + 1,
            'decrease' => $cartItem->quantity - 1,
            default => (int) $request->input('quantity'),
        };

        if ($newQuantity < 1) {
            return back()->with(
                'error',
                'Jumlah produk minimal adalah 1. Gunakan tombol hapus jika ingin mengeluarkan produk.'
            );
        }

        if ($variant->stock < 1) {
            return back()->with('error', 'Stok varian produk ini sedang habis.');
        }

        if ($newQuantity > $variant->stock) {
            return back()->with(
                'error',
                "Jumlah barang melebihi stok varian yang tersedia (maksimal {$variant->stock})."
            );
        }

        $cartItem->update(['quantity' => $newQuantity]);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Jumlah produk di keranjang berhasil diperbarui.');
    }

    /**
     * Menghapus item produk dari keranjang belanja pelanggan.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        if ($cartItem->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus item keranjang ini.');
        }

        $cartItem->delete();

        return redirect()
            ->route('cart.index')
            ->with('success', 'Produk berhasil dihapus dari keranjang belanja.');
    }
}
