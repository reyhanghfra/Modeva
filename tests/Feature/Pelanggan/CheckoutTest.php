<?php

namespace Tests\Feature\Pelanggan;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelanggan;
    protected User $otherPelanggan;
    protected User $admin;
    protected User $petugas;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelanggan = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        Customer::create([
            'user_id' => $this->pelanggan->id,
            'phone' => '081345678901',
            'address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
        ]);

        $this->otherPelanggan = User::factory()->create([
            'name' => 'Maya Lestari',
            'email' => 'maya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);

        $category = Category::factory()->create(['name' => 'Pakaian', 'is_active' => true]);

        $this->product = Product::factory()->for($category)->create([
            'name' => 'Dress Midi Linen',
            'brand' => 'Arunika',
            'price' => 695000,
            'image' => 'https://example.com/dress.jpg',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::factory()->for($this->product)->create([
            'color' => 'Camel',
            'size' => 'M',
            'stock' => 5,
        ]);
    }

    public function test_pelanggan_with_empty_cart_is_redirected_from_checkout(): void
    {
        $response = $this->actingAs($this->pelanggan)->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_pelanggan_can_view_checkout_page_with_cart_summary_and_saved_profile_address(): void
    {
        CartItem::create([
            'user_id' => $this->pelanggan->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->pelanggan)->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Checkout');
        $response->assertSee('Anindya Putri');
        $response->assertSee('081345678901');
        $response->assertSee('Jl. Dago Pakar Barat No. 42, Bandung');
        $response->assertSee('Dress Midi Linen');
        $response->assertSee('Camel');
        $response->assertSee('M');
        $response->assertSee('Rp695.000');
        $response->assertSee('Rp1.390.000');
        $response->assertSee('JNE Reguler');
        $response->assertSee('Bank BCA');
        $response->assertSee('Buat Pesanan');
    }

    public function test_pelanggan_can_submit_checkout_with_profile_address_and_decrements_stock(): void
    {
        CartItem::create([
            'user_id' => $this->pelanggan->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->pelanggan)->post(route('checkout.store'), [
            'recipient_name' => 'Anindya Putri',
            'recipient_phone' => '081345678901',
            'address_option' => 'profile',
            'shipping_service' => 'J&T Express',
            'payment_method' => 'transfer',
            'bank_name' => 'BCA',
        ]);

        $order = Order::where('user_id', $this->pelanggan->id)->firstOrFail();

        $response->assertRedirect(route('checkout.success', $order));
        $response->assertSessionHas('success');

        $this->assertSame('J&T Express', $order->shipping_service);
        $this->assertSame(1390000, $order->total);
        $this->assertSame(Order::STATUS_MENUNGGU_PEMBAYARAN, $order->status);
        $this->assertStringContainsString('Jl. Dago Pakar Barat No. 42, Bandung', $order->shipping_address);

        // Detail item tersimpan
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $this->product->id,
            'product_name' => 'Dress Midi Linen',
            'variant' => 'Camel · M',
            'quantity' => 2,
            'price' => 695000,
            'subtotal' => 1390000,
        ]);

        // Data pembayaran awal tersimpan
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'method' => 'transfer',
            'bank_name' => 'BCA',
            'sender_name' => 'Anindya Putri',
            'amount' => 1390000,
            'status' => Payment::STATUS_BELUM_DIBAYAR,
        ]);

        // Stok berkurang dari 5 menjadi 3 dan keranjang dikosongkan
        $this->assertSame(3, $this->variant->fresh()->stock);
        $this->assertSame(0, CartItem::where('user_id', $this->pelanggan->id)->count());

        // Halaman konfirmasi pesanan dapat dibuka oleh pemilik pesanan
        $successPage = $this->actingAs($this->pelanggan)->get(route('checkout.success', $order));
        $successPage->assertStatus(200);
        $successPage->assertSee($order->order_number);
        $successPage->assertSee('Pesanan Berhasil Dibuat');
        $successPage->assertSee('Rp1.390.000');
    }

    public function test_pelanggan_can_submit_checkout_with_custom_shipping_address(): void
    {
        CartItem::create([
            'user_id' => $this->pelanggan->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($this->pelanggan)->post(route('checkout.store'), [
            'recipient_name' => 'Budi Penerima',
            'recipient_phone' => '081299998888',
            'address_option' => 'custom',
            'shipping_address' => 'Jl. Sudirman Kav. 50, Jakarta Selatan',
            'shipping_service' => 'SiCepat BEST',
            'payment_method' => 'transfer',
            'bank_name' => 'Mandiri',
        ]);

        $order = Order::where('user_id', $this->pelanggan->id)->firstOrFail();
        $response->assertRedirect(route('checkout.success', $order));

        $this->assertStringContainsString('Budi Penerima', $order->shipping_address);
        $this->assertStringContainsString('Jl. Sudirman Kav. 50, Jakarta Selatan', $order->shipping_address);
        $this->assertSame('SiCepat BEST', $order->shipping_service);
    }

    public function test_checkout_aborts_and_prevents_negative_stock_when_stock_is_insufficient(): void
    {
        CartItem::create([
            'user_id' => $this->pelanggan->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 10, // melebihi stok 5
        ]);

        $response = $this->actingAs($this->pelanggan)->post(route('checkout.store'), [
            'recipient_name' => 'Anindya Putri',
            'recipient_phone' => '081345678901',
            'address_option' => 'profile',
            'shipping_service' => 'JNE Reguler',
            'payment_method' => 'transfer',
            'bank_name' => 'BCA',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        // Tidak ada order atau order_items yang terbuat, stok tetap 5, keranjang tidak terhapus
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(5, $this->variant->fresh()->stock);
        $this->assertSame(1, CartItem::where('user_id', $this->pelanggan->id)->count());
    }

    public function test_pelanggan_cannot_view_another_customers_checkout_success_page(): void
    {
        $order = Order::create([
            'order_number' => 'MDV-OTHER-01',
            'user_id' => $this->otherPelanggan->id,
            'shipping_address' => 'Surabaya',
            'shipping_service' => 'JNE Reguler',
            'total' => 500000,
            'status' => Order::STATUS_MENUNGGU_PEMBAYARAN,
        ]);

        $this->actingAs($this->pelanggan)
            ->get(route('checkout.success', $order))
            ->assertStatus(403);
    }

    public function test_non_pelanggan_cannot_access_checkout(): void
    {
        $this->actingAs($this->admin)->get(route('checkout.index'))->assertStatus(403);
        $this->actingAs($this->petugas)->get(route('checkout.index'))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_checkout(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }

    public function test_pelanggan_can_checkout_using_saved_customer_address_and_cannot_use_others_address(): void
    {
        $savedAddress = CustomerAddress::create([
            'user_id' => $this->pelanggan->id,
            'recipient_name' => 'Anindya Rumah',
            'phone' => '081345678901',
            'address' => 'Jl. Cihampelas No. 120',
            'district' => 'Coblong',
            'city' => 'Kota Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40131',
            'is_primary' => true,
        ]);

        $otherAddress = CustomerAddress::create([
            'user_id' => $this->otherPelanggan->id,
            'recipient_name' => 'Maya Surabaya',
            'phone' => '081567890123',
            'address' => 'Jl. Darmo No. 1',
            'district' => 'Wonokromo',
            'city' => 'Kota Surabaya',
            'province' => 'Jawa Timur',
            'postal_code' => '60241',
            'is_primary' => true,
        ]);

        CartItem::create([
            'user_id' => $this->pelanggan->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        // Tidak boleh memakai alamat milik pelanggan lain
        $this->actingAs($this->pelanggan)->post(route('checkout.store'), [
            'recipient_name' => 'Anindya Rumah',
            'recipient_phone' => '081345678901',
            'address_option' => 'saved_address',
            'selected_address_id' => $otherAddress->id,
            'shipping_service' => 'JNE Reguler',
            'payment_method' => 'transfer',
            'bank_name' => 'BCA',
        ])->assertStatus(403);

        // Checkout memakai alamat tersimpan milik sendiri berhasil
        $response = $this->actingAs($this->pelanggan)->post(route('checkout.store'), [
            'recipient_name' => 'Anindya Rumah',
            'recipient_phone' => '081345678901',
            'address_option' => 'saved_address',
            'selected_address_id' => $savedAddress->id,
            'shipping_service' => 'JNE Reguler',
            'payment_method' => 'transfer',
            'bank_name' => 'BCA',
        ]);

        $order = Order::where('user_id', $this->pelanggan->id)->firstOrFail();
        $response->assertRedirect(route('checkout.success', $order));
        $this->assertStringContainsString('Jl. Cihampelas No. 120, Kec. Coblong, Kota Bandung, Jawa Barat 40131', $order->shipping_address);
    }
}
