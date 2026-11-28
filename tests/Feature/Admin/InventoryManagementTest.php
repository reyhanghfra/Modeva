<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_view_stock_summary_and_statuses_for_variants(): void
    {
        $product = $this->product('MDV-INV-101', 'Loafer Kota');
        $product->variants()->createMany([
            ['color' => 'Hitam', 'size' => '38', 'stock' => 10, 'low_stock_threshold' => 5],
            ['color' => 'Camel', 'size' => '39', 'stock' => 5, 'low_stock_threshold' => 5],
            ['color' => 'Taupe', 'size' => '40', 'stock' => 0, 'low_stock_threshold' => 5],
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.inventory.index'));

        $response->assertOk()
            ->assertViewHas('summary', fn ($summary): bool => (int) $summary->total_units === 15
                && (int) $summary->low_stock_count === 1
                && (int) $summary->out_of_stock_count === 1)
            ->assertSee('Loafer Kota')
            ->assertSee('MDV-INV-101')
            ->assertSee('Aman')
            ->assertSee('Menipis')
            ->assertSee('Habis');
    }

    public function test_inventory_filter_searches_product_and_limits_to_stock_status(): void
    {
        $lowStockProduct = $this->product('MDV-INV-201', 'Tas Kanvas');
        $lowStockProduct->variants()->create([
            'color' => 'Krem',
            'size' => 'Satu Ukuran',
            'stock' => 2,
            'low_stock_threshold' => 5,
        ]);
        $safeProduct = $this->product('MDV-INV-202', 'Tas Kulit');
        $safeProduct->variants()->create([
            'color' => 'Hitam',
            'size' => 'Satu Ukuran',
            'stock' => 10,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.inventory.index', [
            'status' => 'menipis',
            'search' => 'MDV-INV-201',
        ]));

        $response->assertOk()
            ->assertSee('Tas Kanvas')
            ->assertSee('Menipis')
            ->assertDontSee('Tas Kulit');
    }

    public function test_admin_can_add_to_variant_stock_and_update_its_threshold(): void
    {
        $variant = $this->variantWithStock(4);

        $response = $this->actingAs($this->admin())->patch(
            route('admin.inventory.stock.update', $variant),
            [
                'operation' => 'add',
                'quantity' => 3,
                'low_stock_threshold' => 6,
            ]
        );

        $response->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock' => 7,
            'low_stock_threshold' => 6,
        ]);
    }

    public function test_admin_can_set_or_subtract_variant_stock(): void
    {
        $variant = $this->variantWithStock(10);

        $setResponse = $this->actingAs($this->admin())->patch(
            route('admin.inventory.stock.update', $variant),
            ['operation' => 'set', 'quantity' => 7, 'low_stock_threshold' => 5]
        );
        $setResponse->assertRedirect();
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 7]);

        $subtractResponse = $this->actingAs($this->admin())->patch(
            route('admin.inventory.stock.update', $variant),
            ['operation' => 'subtract', 'quantity' => 2, 'low_stock_threshold' => 5]
        );
        $subtractResponse->assertRedirect();
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 5]);
    }

    public function test_subtracting_more_than_available_stock_is_rejected_without_changing_stock(): void
    {
        $variant = $this->variantWithStock(3);

        $response = $this->actingAs($this->admin())->patch(
            route('admin.inventory.stock.update', $variant),
            ['operation' => 'subtract', 'quantity' => 4, 'low_stock_threshold' => 5]
        );

        $response->assertRedirect()
            ->assertSessionHasErrors([
                'quantity' => 'Jumlah pengurangan melebihi stok yang tersedia.',
            ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $variant->id,
            'stock' => 3,
            'low_stock_threshold' => 5,
        ]);
    }

    public function test_inventory_pages_are_forbidden_to_non_admin_users(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($customer)->get(route('admin.inventory.index'));

        $response->assertForbidden();
    }

    private function product(string $sku, string $name): Product
    {
        $category = Category::factory()->create();

        return Product::factory()->for($category)->create([
            'sku' => $sku,
            'name' => $name,
        ]);
    }

    private function variantWithStock(int $stock): ProductVariant
    {
        return $this->product('MDV-INV-'.fake()->unique()->numerify('###'), 'Blazer Inventori')
            ->variants()
            ->create([
                'color' => 'Arang',
                'size' => 'M',
                'stock' => $stock,
                'low_stock_threshold' => 5,
            ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
