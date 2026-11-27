<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pelanggan\StoreCheckoutRequest;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Support\Rupiah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    /**
     * Daftar jasa pengiriman yang tersedia di Modeva.
     *
     * @return array<int, array{code: string, name: string, estimate: string}>
     */
    public static function shippingServices(): array
    {
        return [
            ['code' => 'JNE Reguler', 'name' => 'JNE Reguler', 'estimate' => 'Estimasi 2–4 hari kerja'],
            ['code' => 'J&T Express', 'name' => 'J&T Express', 'estimate' => 'Estimasi 2–3 hari kerja'],
            ['code' => 'SiCepat BEST', 'name' => 'SiCepat BEST', 'estimate' => 'Estimasi 1–2 hari kerja'],
            ['code' => 'JNE YES', 'name' => 'JNE YES', 'estimate' => 'Estimasi 1 hari kerja'],
        ];
    }

    /**
     * Daftar bank tujuan transfer yang tersedia di Modeva.
     *
     * @return array<int, array{code: string, label: string, account: string}>
     */
    public static function paymentBanks(): array
    {
        return [
            ['code' => 'BCA', 'label' => 'Bank BCA', 'account' => '1234-5678-90 a.n. Modeva Fashion Store'],
            ['code' => 'Mandiri', 'label' => 'Bank Mandiri', 'account' => '1400-0987-6543-2 a.n. Modeva Fashion Store'],
            ['code' => 'BRI', 'label' => 'Bank BRI', 'account' => '0321-0100-4455-509 a.n. Modeva Fashion Store'],
            ['code' => 'BNI', 'label' => 'Bank BNI', 'account' => '0899-7766-55 a.n. Modeva Fashion Store'],
        ];
    }

    /**
     * Menampilkan halaman checkout berdasarkan isi keranjang pelanggan.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load([
            'customer',
            'addresses' => fn ($query) => $query->orderByDesc('is_primary')->latest('id'),
        ]);

        $cartItems = CartItem::query()
            ->where('user_id', $user->id)
            ->with(['variant.product'])
            ->latest('id')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang belanja Anda masih kosong. Tambahkan produk terlebih dahulu sebelum checkout.');
        }

        $totalItems = (int) $cartItems->sum('quantity');
        $subtotal = (int) $cartItems->sum(fn (CartItem $item): int => $item->subtotal);
        $shippingCost = 0; // Belum ada tabel/mekanisme tarif ongkos kirim otomatis
        $total = $subtotal + $shippingCost;

        $formattedSubtotal = Rupiah::format($subtotal);
        $formattedShippingCost = Rupiah::format($shippingCost);
        $formattedTotal = Rupiah::format($total);

        $shippingServices = self::shippingServices();
        $paymentBanks = self::paymentBanks();
        $customer = $user->customer;
        $addresses = $user->addresses;
        $primaryAddress = $addresses->firstWhere('is_primary', true) ?? $addresses->first();

        return view('pelanggan.checkout.index', compact(
            'user',
            'customer',
            'addresses',
            'primaryAddress',
            'cartItems',
            'totalItems',
            'subtotal',
            'shippingCost',
            'total',
            'formattedSubtotal',
            'formattedShippingCost',
            'formattedTotal',
            'shippingServices',
            'paymentBanks'
        ));
    }

    /**
     * Memproses checkout dan menyimpan transaksi, item pesanan, serta data pembayaran awal secara atomik.
     */
    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $user = $request->user()->load('customer');
        $validated = $request->validated();

        $addressOption = $validated['address_option'] ?? null;

        if ($addressOption === 'saved_address') {
            $selectedAddress = $user->addresses()->find($validated['selected_address_id'] ?? null);
            if (! $selectedAddress) {
                abort(403, 'Anda tidak memiliki akses ke alamat pengiriman yang dipilih.');
            }
            $rawAddress = $selectedAddress->formatted_full_address;
        } elseif ($addressOption === 'profile' && filled($user->customer?->address)) {
            $rawAddress = trim((string) $user->customer->address);
        } else {
            $rawAddress = trim((string) $validated['shipping_address']);
        }

        $formattedShippingAddress = sprintf(
            '%s (%s) — %s',
            trim($validated['recipient_name']),
            trim($validated['recipient_phone']),
            $rawAddress
        );

        try {
            $order = DB::transaction(function () use ($user, $validated, $formattedShippingAddress): Order {
                $cartItems = CartItem::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new InvalidArgumentException('Keranjang belanja Anda kosong.');
                }

                $variants = ProductVariant::query()
                    ->whereIn('id', $cartItems->pluck('product_variant_id'))
                    ->with('product')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $total = 0;
                $orderItemsPayload = [];

                foreach ($cartItems as $item) {
                    $variant = $variants->get($item->product_variant_id);
                    $product = $variant?->product;

                    if (! $variant || ! $product || ! $product->is_active) {
                        throw new InvalidArgumentException('Salah satu produk di keranjang sudah tidak tersedia.');
                    }

                    if ($variant->stock < $item->quantity) {
                        throw new InvalidArgumentException(
                            "Stok {$product->name} ({$variant->color} · {$variant->size}) tidak mencukupi (tersisa {$variant->stock})."
                        );
                    }

                    $unitPrice = (int) round((float) $product->price);
                    $subtotal = $unitPrice * $item->quantity;
                    $total += $subtotal;

                    $orderItemsPayload[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'variant' => "{$variant->color} · {$variant->size}",
                        'quantity' => $item->quantity,
                        'price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ];

                    $variant->decrement('stock', $item->quantity);
                }

                $order = Order::create([
                    'order_number' => $this->generateUniqueOrderNumber(),
                    'user_id' => $user->id,
                    'shipping_address' => $formattedShippingAddress,
                    'shipping_service' => $validated['shipping_service'],
                    'total' => $total,
                    'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
                ]);

                $order->items()->createMany($orderItemsPayload);

                $order->payments()->create([
                    'method' => $validated['payment_method'],
                    'bank_name' => $validated['bank_name'],
                    'sender_name' => trim($validated['recipient_name']),
                    'amount' => $total,
                    'status' => Payment::STATUS_BELUM_DIBAYAR,
                ]);

                CartItem::where('user_id', $user->id)->delete();

                return $order;
            });
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('checkout.success', $order)
            ->with('success', 'Pesanan Anda berhasil dibuat. Silakan selesaikan pembayaran sesuai instruksi.');
    }

    /**
     * Menampilkan halaman konfirmasi & ringkasan pesanan setelah checkout berhasil.
     */
    public function success(Request $request, Order $order): View
    {
        if ($order->user_id !== $request->user()->id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat pesanan ini.');
        }

        $order->load(['items', 'latestPayment']);

        return view('pelanggan.checkout.success', compact('order'));
    }

    /**
     * Membuat nomor transaksi unik dengan format MDV-YYYYMMDDXXXX.
     */
    protected function generateUniqueOrderNumber(): string
    {
        do {
            $orderNumber = 'MDV-' . now()->format('ymd') . strtoupper(Str::random(4));
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}
