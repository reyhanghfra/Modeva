<?php

namespace Tests\Feature\Storefront;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public static function catalogSearchTerms(): array
    {
        return [
            'product name' => ['Kemeja Linen'],
            'brand' => ['Kala Studio'],
            'sku' => ['MDV-SEARCH-001'],
        ];
    }

    #[DataProvider('catalogSearchTerms')]
    public function test_catalog_search_finds_products_by_name_brand_or_sku(string $search): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create([
            'name' => 'Kemeja Linen',
            'brand' => 'Kala Studio',
            'sku' => 'MDV-SEARCH-001',
        ]);
        Product::factory()->for($category)->create([
            'name' => 'Kemeja Linen Arsip',
            'brand' => 'Kala Studio',
            'sku' => 'MDV-SEARCH-002',
            'is_active' => false,
        ]);

        $response = $this->get(route('products.index', ['search' => $search]));

        $response->assertOk()
            ->assertSee('Kemeja Linen')
            ->assertDontSee('Kemeja Linen Arsip');
    }

    public function test_catalog_lists_only_active_products_and_filters_by_category(): void
    {
        $clothingCategory = Category::factory()->create(['name' => 'Pakaian']);
        $bagCategory = Category::factory()->create(['name' => 'Tas']);
        $clothing = Product::factory()->for($clothingCategory)->create([
            'name' => 'Kemeja Linen',
            'brand' => 'Kala Studio',
            'price' => 245000,
        ]);
        $clothing->variants()->createMany([
            ['color' => 'Taupe', 'size' => 'S', 'stock' => 4, 'low_stock_threshold' => 5],
            ['color' => 'Krem', 'size' => 'M', 'stock' => 8, 'low_stock_threshold' => 5],
        ]);
        Product::factory()->for($clothingCategory)->create([
            'name' => 'Kemeja Arsip',
            'is_active' => false,
        ]);
        Product::factory()->for($bagCategory)->create([
            'name' => 'Tas Harian',
        ]);
        Product::factory()->for($bagCategory)->create([
            'name' => 'Tas Tanpa Varian',
            'image' => null,
        ]);

        $response = $this->get(route('products.index', ['category_id' => $clothingCategory->id]));

        $response->assertOk()
            ->assertSee('Kemeja Linen')
            ->assertSee('Rp245.000')
            ->assertSee('2 warna')
            ->assertSee('2 varian tersedia')
            ->assertDontSee('Kemeja Arsip')
            ->assertDontSee('Tas Harian');

        $emptyStockResponse = $this->get(route('products.index', ['search' => 'Tas Tanpa Varian']));
        $emptyStockResponse->assertSee('Habis');
    }

    public function test_catalog_sorting_and_pagination_preserve_search_parameters(): void
    {
        $category = Category::factory()->create();

        foreach (range(1, 13) as $index) {
            Product::factory()->for($category)->create([
                'name' => 'Koleksi Pagi '.$index,
                'brand' => 'Atelier Test',
                'price' => 100000 + ($index * 1000),
            ]);
        }

        $response = $this->get(route('products.index', [
            'search' => 'Koleksi Pagi',
            'sort' => 'price_desc',
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertViewHas('products', fn ($products): bool => $products->currentPage() === 2
                && $products->count() === 1
                && $products->first()->price === '101000.00')
            ->assertSee('search=Koleksi%20Pagi')
            ->assertSee('sort=price_desc');
    }

    public function test_product_detail_shows_information_and_variant_stock_data(): void
    {
        $category = Category::factory()->create(['name' => 'Pakaian']);
        $product = Product::factory()->for($category)->create([
            'name' => 'Kemeja Detail',
            'brand' => 'Sora',
            'sku' => 'MDV-DETAIL-001',
            'description' => 'Kemeja linen pilihan untuk kegiatan sehari-hari.',
            'price' => 320000,
        ]);
        $variant = $product->variants()->create([
            'color' => 'Taupe',
            'size' => 'M',
            'stock' => 12,
            'low_stock_threshold' => 5,
        ]);
        $product->variants()->create([
            'color' => 'Hitam',
            'size' => 'M',
            'stock' => 0,
            'low_stock_threshold' => 5,
        ]);

        $response = $this->get(route('products.show', ['product' => $product->slug]));

        $response->assertOk()
            ->assertSee('Sora')
            ->assertSee('Kemeja Detail')
            ->assertSee('MDV-DETAIL-001')
            ->assertSee('Rp320.000')
            ->assertSee('Kemeja linen pilihan')
            ->assertSee('Tambah ke Keranjang')
            ->assertSee('Keranjang belum tersedia')
            ->assertSee('Stok menipis');

        $variantOptions = $response->viewData('variantOptions');
        $this->assertSame($variant->id, $variantOptions[0]['id']);
        $this->assertSame(12, $variantOptions[0]['stock']);
    }

    public function test_inactive_product_detail_returns_not_found(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['is_active' => false]);

        $response = $this->get(route('products.show', ['product' => $product->slug]));

        $response->assertNotFound();
    }

    public function test_related_products_are_limited_to_four_active_products_in_the_same_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['name' => 'Produk Utama']);

        foreach (range(1, 5) as $index) {
            Product::factory()->for($category)->create(['name' => 'Terkait '.$index]);
        }

        Product::factory()->for($category)->create([
            'name' => 'Produk Nonaktif',
            'is_active' => false,
        ]);
        Product::factory()->create(['name' => 'Kategori Lain']);

        $response = $this->get(route('products.show', ['product' => $product->slug]));

        $response->assertOk()
            ->assertViewHas('relatedProducts', fn ($relatedProducts): bool => $relatedProducts->count() === 4
                && $relatedProducts->every(fn (Product $relatedProduct): bool => $relatedProduct->is_active
                    && $relatedProduct->category_id === $product->category_id
                    && $relatedProduct->id !== $product->id));
    }

    public function test_home_page_links_to_the_catalog_without_broken_customer_order_routes(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee(route('products.index'))
            ->assertSee('Belanja Sekarang');
    }
}
