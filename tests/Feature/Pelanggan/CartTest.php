<?php

namespace Tests\Feature\Pelanggan;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CartItemSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected User $pelangganA;
    protected User $pelangganB;
    protected User $admin;
    protected User $petugas;
    protected Product $product;
    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pelangganA = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->pelangganB = User::factory()->create([
            'name' => 'Maya Lestari',
            'email' => 'maya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->petugas = User::factory()->create(['role' => 'petugas', 'is_active' => true]);

        $category = Category::factory()->create(['name' => 'Pakaian', 'is_active' => true]);

        $this->product = Product::factory()->for($category)->create([
            'name' => 'Kemeja Linen Oversize',
            'brand' => 'Kala Studio',
            'price' => 425000,
            'image' => 'https://example.com/kemeja.jpg',
            'is_active' => true,
        ]);

        $this->variant = ProductVariant::factory()->for($this->product)->create([
            'color' => 'Taupe',
            'size' => 'M',
            'stock' => 3,
        ]);
    }

    public function test_pelanggan_can_view_cart_page_with_storefront_layout_and_item_details(): void
    {
        CartItem::create([
            'user_id' => $this->pelangganA->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->pelangganA)->get(route('cart.index'));

        $response->assertStatus(200);
        // Storefront navbar & footer
        $response->assertSee('MODEVA');
        $response->assertSee('Beranda');
        $response->assertSee('Belanja');
        $response->assertSee('Kategori');
        $response->assertSee('Lacak Pesanan');
        $response->assertSee('halo@modeva.id');

        // Item detail (gambar, merek, nama, ukuran, warna, harga, jumlah, subtotal, total)
        $response->assertSee('https://example.com/kemeja.jpg');
        $response->assertSee('Kala Studio');
        $response->assertSee('Kemeja Linen Oversize');
        $response->assertSee('Taupe');
        $response->assertSee('M');
        $response->assertSee('Rp425.000');
        $response->assertSee('Rp850.000');
        $response->assertSee('Lanjutkan Belanja');
        $response->assertSee('Lanjut ke Checkout');
        $response->assertSee(route('checkout.index'));
    }

    public function test_pelanggan_can_add_product_variant_and_validates_stock(): void
    {
        $response = $this->actingAs($this->pelangganA)->post(route('cart.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->pelangganA->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        // Menambah lagi hingga melebihi stok (2 + 2 > 3) harus ditolak
        $overLimit = $this->actingAs($this->pelangganA)->post(route('cart.store'), [
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        $overLimit->assertSessionHas('error');
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $this->pelangganA->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_pelanggan_can_increase_decrease_and_delete_cart_item(): void
    {
        $cartItem = CartItem::create([
            'user_id' => $this->pelangganA->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 2,
        ]);

        // Tambah jadi 3 (batas stok 3)
        $this->actingAs($this->pelangganA)
            ->patch(route('cart.update', $cartItem), ['action' => 'increase'])
            ->assertRedirect(route('cart.index'));
        $this->assertSame(3, $cartItem->fresh()->quantity);

        // Tambah lagi jadi 4 (melebihi stok 3) harus ditolak
        $this->actingAs($this->pelangganA)
            ->patch(route('cart.update', $cartItem), ['action' => 'increase'])
            ->assertSessionHas('error');
        $this->assertSame(3, $cartItem->fresh()->quantity);

        // Kurangi jadi 2
        $this->actingAs($this->pelangganA)
            ->patch(route('cart.update', $cartItem), ['action' => 'decrease'])
            ->assertRedirect(route('cart.index'));
        $this->assertSame(2, $cartItem->fresh()->quantity);

        // Hapus item
        $this->actingAs($this->pelangganA)
            ->delete(route('cart.destroy', $cartItem))
            ->assertRedirect(route('cart.index'));
        $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    }

    public function test_carts_are_isolated_between_customers(): void
    {
        $cartItemA = CartItem::create([
            'user_id' => $this->pelangganA->id,
            'product_variant_id' => $this->variant->id,
            'quantity' => 1,
        ]);

        // Pelanggan B tidak melihat item milik Pelanggan A
        $this->actingAs($this->pelangganB)
            ->get(route('cart.index'))
            ->assertStatus(200)
            ->assertSee('Keranjang Belanja Masih Kosong')
            ->assertDontSee('Kemeja Linen Oversize');

        // Pelanggan B tidak boleh mengubah atau menghapus item milik Pelanggan A
        $this->actingAs($this->pelangganB)
            ->patch(route('cart.update', $cartItemA), ['action' => 'increase'])
            ->assertStatus(403);

        $this->actingAs($this->pelangganB)
            ->delete(route('cart.destroy', $cartItemA))
            ->assertStatus(403);

        $this->assertDatabaseHas('cart_items', ['id' => $cartItemA->id]);
    }

    public function test_non_pelanggan_cannot_access_cart(): void
    {
        $this->actingAs($this->admin)->get(route('cart.index'))->assertStatus(403);
        $this->actingAs($this->petugas)->get(route('cart.index'))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_cart(): void
    {
        $this->get(route('cart.index'))->assertRedirect(route('login'));
    }

    public function test_cart_item_seeder_populates_demo_customer_cart_idempotently(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(ProductSeeder::class);
        $this->seed(CartItemSeeder::class);
        $this->seed(CartItemSeeder::class);

        $demoCustomer = User::where('email', 'pelanggan@modeva.test')->firstOrFail();
        $this->assertSame(2, CartItem::where('user_id', $demoCustomer->id)->count());
    }
}
