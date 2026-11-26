<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $pelanggan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->pelanggan = User::factory()->create([
            'role' => 'pelanggan',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_category_index_and_search(): void
    {
        Category::factory()->create(['name' => 'Koleksi Sepatu']);
        Category::factory()->create(['name' => 'Koleksi Tas']);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index', [
            'search' => 'Sepatu',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Koleksi Sepatu');
        $response->assertDontSee('Koleksi Tas');
    }

    public function test_admin_can_create_category_with_automatic_slug(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Pakaian Pria Mewah',
            'description' => 'Koleksi busana pria terbaik.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Pakaian Pria Mewah',
            'slug' => 'pakaian-pria-mewah',
            'is_active' => 1,
        ]);
    }

    public function test_category_slug_collision_handled(): void
    {
        Category::factory()->create([
            'name' => 'Koleksi Musim Panas',
            'slug' => 'koleksi-musim-panas',
        ]);

        // Nama berbeda tetapi menghasilkan slug awal yang identik
        $cat2 = Category::create([
            'name' => 'Koleksi: Musim Panas',
        ]);

        $this->assertSame('koleksi-musim-panas-1', $cat2->slug);
    }

    public function test_admin_can_update_category_and_slug_updates(): void
    {
        $category = Category::factory()->create([
            'name' => 'Nama Lama',
            'slug' => 'nama-lama',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Nama Baru Keren',
            'description' => 'Deskripsi baru.',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $category->refresh();
        $this->assertSame('Nama Baru Keren', $category->name);
        $this->assertSame('nama-baru-keren', $category->slug);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_category_cannot_be_deleted_if_used_by_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->for($category)->create();

        $response = $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_non_admin_cannot_access_categories_management(): void
    {
        $response = $this->actingAs($this->pelanggan)->get(route('admin.categories.index'));
        $response->assertStatus(403);
    }
}
