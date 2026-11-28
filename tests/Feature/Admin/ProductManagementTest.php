<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_admin_can_search_and_filter_products_with_total_stock(): void
    {
        $category = Category::factory()->create(['name' => 'Pakaian']);
        $otherCategory = Category::factory()->create(['name' => 'Tas']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Kemeja Linen',
            'sku' => 'MDV-PAK-801',
            'brand' => 'Kala Studio',
        ]);
        $product->variants()->createMany([
            ['color' => 'Taupe', 'size' => 'S', 'stock' => 4, 'low_stock_threshold' => 5],
            ['color' => 'Krem', 'size' => 'M', 'stock' => 7, 'low_stock_threshold' => 5],
        ]);
        Product::factory()->for($otherCategory)->create([
            'name' => 'Tas Harian',
            'sku' => 'MDV-TAS-801',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.index', [
            'search' => 'MDV-PAK-801',
            'category_id' => $category->id,
        ]));

        $response->assertOk()
            ->assertSee('Kemeja Linen')
            ->assertSee('Kala Studio')
            ->assertSee('11 pcs')
            ->assertDontSee('Tas Harian');
    }

    public function test_admin_can_create_product_with_variants_and_public_image(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), [
            ...$this->validProductData($category),
            'image' => UploadedFile::fake()->image('look.jpg'),
        ]);

        $response->assertRedirectToRoute('admin.products.index')
            ->assertSessionHas('success');

        $product = Product::query()->where('sku', 'MDV-PAK-900')->firstOrFail();
        $this->assertSame('kemeja-linen-pagi', $product->slug);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'color' => 'Taupe',
            'size' => 'M',
            'stock' => 8,
            'low_stock_threshold' => 5,
        ]);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_open_create_and_edit_forms_with_variant_fields(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create([
            'name' => 'Kemeja Tampilan',
        ]);
        $variant = $product->variants()->create([
            'color' => 'Krem',
            'size' => 'L',
            'stock' => 6,
            'low_stock_threshold' => 5,
        ]);

        $createResponse = $this->actingAs($this->admin())->get(route('admin.products.create'));
        $createResponse->assertOk()
            ->assertSee('Tambah Produk')
            ->assertSee('variants[0][color]', false);

        $editResponse = $this->get(route('admin.products.edit', $product));
        $editResponse->assertOk()
            ->assertSee('Kemeja Tampilan')
            ->assertSee('name="variants[0][id]" value="'.$variant->id.'"', false)
            ->assertSee('Krem');
    }

    public function test_duplicate_variant_combinations_are_rejected_without_creating_a_product(): void
    {
        $category = Category::factory()->create();
        $data = $this->validProductData($category);
        $data['variants'][] = [
            'color' => 'taupe',
            'size' => 'm',
            'stock' => 2,
            'low_stock_threshold' => 5,
        ];

        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), $data);

        $response->assertRedirect()
            ->assertSessionHasErrors(['variants.1.color']);
        $this->assertDatabaseMissing('products', ['sku' => 'MDV-PAK-900']);
    }

    public function test_duplicate_skus_are_rejected_with_a_validation_message(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create(['sku' => 'MDV-PAK-900']);

        $response = $this->actingAs($this->admin())->post(route('admin.products.store'), $this->validProductData($category));

        $response->assertRedirect()
            ->assertSessionHasErrors([
                'sku' => 'SKU tersebut sudah digunakan.',
            ]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_admin_can_update_product_and_sync_its_variants(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create([
            'name' => 'Kemeja Lama',
            'sku' => 'MDV-PAK-901',
        ]);
        $keptVariant = $product->variants()->create([
            'color' => 'Taupe',
            'size' => 'M',
            'stock' => 2,
            'low_stock_threshold' => 5,
        ]);
        $removedVariant = $product->variants()->create([
            'color' => 'Hitam',
            'size' => 'L',
            'stock' => 4,
            'low_stock_threshold' => 5,
        ]);
        $data = $this->validProductData($category);
        $data['sku'] = 'MDV-PAK-902';
        $data['name'] = 'Kemeja Linen Baru';
        $data['variants'] = [
            [
                'id' => $keptVariant->id,
                'color' => 'Taupe',
                'size' => 'M',
                'stock' => 9,
                'low_stock_threshold' => 4,
            ],
            [
                'color' => 'Hitam',
                'size' => 'L',
                'stock' => 3,
                'low_stock_threshold' => 5,
            ],
        ];

        $response = $this->actingAs($this->admin())->put(route('admin.products.update', $product), $data);

        $response->assertRedirectToRoute('admin.products.index')
            ->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sku' => 'MDV-PAK-902',
            'slug' => 'kemeja-linen-baru',
        ]);
        $this->assertDatabaseHas('product_variants', [
            'id' => $keptVariant->id,
            'stock' => 9,
            'low_stock_threshold' => 4,
        ]);
        $this->assertDatabaseMissing('product_variants', ['id' => $removedVariant->id]);
        $this->assertDatabaseHas('product_variants', [
            'product_id' => $product->id,
            'color' => 'Hitam',
            'size' => 'L',
        ]);
    }

    public function test_product_update_rejects_variant_ids_owned_by_another_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create();
        $otherProduct = Product::factory()->for($category)->create();
        $foreignVariant = $otherProduct->variants()->create([
            'color' => 'Hitam',
            'size' => 'L',
            'stock' => 2,
            'low_stock_threshold' => 5,
        ]);
        $data = $this->validProductData($category);
        $data['variants'][0]['id'] = $foreignVariant->id;

        $response = $this->actingAs($this->admin())->put(route('admin.products.update', $product), $data);

        $response->assertRedirect()
            ->assertSessionHasErrors(['variants.0.id']);
        $this->assertDatabaseHas('product_variants', [
            'id' => $foreignVariant->id,
            'product_id' => $otherProduct->id,
        ]);
    }

    public function test_admin_can_delete_product_and_its_public_image(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $imagePath = 'products/remove-me.jpg';
        Storage::disk('public')->put($imagePath, 'image contents');
        $product = Product::factory()->for($category)->create(['image' => $imagePath]);
        $variant = $product->variants()->create([
            'color' => 'Taupe',
            'size' => 'M',
            'stock' => 4,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->actingAs($this->admin())->delete(route('admin.products.destroy', $product));

        $response->assertRedirectToRoute('admin.products.index')
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_product_pages_are_forbidden_to_non_admin_users(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);

        $response = $this->actingAs($customer)->get(route('admin.products.index'));

        $response->assertForbidden();
    }

    public function test_product_list_escapes_untrusted_product_names(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create([
            'name' => '<script>alert("xss")</script>',
        ]);

        $response = $this->actingAs($this->admin())->get(route('admin.products.index'));

        $response->assertOk()
            ->assertSee('<script>alert("xss")</script>')
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    /**
     * @return array<string, mixed>
     */
    private function validProductData(Category $category): array
    {
        return [
            'category_id' => $category->id,
            'sku' => 'MDV-PAK-900',
            'name' => 'Kemeja Linen Pagi',
            'brand' => 'Kala Studio',
            'description' => 'Kemeja linen berpotongan santai untuk koleksi harian.',
            'price' => 425000,
            'is_active' => 1,
            'variants' => [
                [
                    'color' => 'Taupe',
                    'size' => 'M',
                    'stock' => 8,
                    'low_stock_threshold' => 5,
                ],
            ],
        ];
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }
}
