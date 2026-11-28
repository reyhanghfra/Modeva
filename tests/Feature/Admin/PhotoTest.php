<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_admin_can_upload_and_replace_hero_photo(): void
    {
        $this->actingAs($this->admin)->post(route('admin.photos.update'), [
            'type' => 'hero', 'image' => UploadedFile::fake()->image('a.jpg'),
        ])->assertRedirect();

        $first = DB::table('site_settings')->where('key', 'hero')->value('value');
        Storage::disk('public')->assertExists($first);

        $this->actingAs($this->admin)->post(route('admin.photos.update'), [
            'type' => 'hero', 'image' => UploadedFile::fake()->image('b.jpg'),
        ]);

        Storage::disk('public')->assertMissing($first);
    }

    public function test_admin_can_set_category_photo(): void
    {
        $cat = Category::create(['name' => 'Tas', 'is_active' => true]);

        $this->actingAs($this->admin)->post(route('admin.photos.update'), [
            'type' => 'category', 'id' => $cat->id, 'image' => UploadedFile::fake()->image('c.png'),
        ]);

        Storage::disk('public')->assertExists($cat->fresh()->image);
    }

    public function test_invalid_file_and_non_admin_are_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.photos.update'), [
            'type' => 'hero', 'image' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('image');

        $pelanggan = User::factory()->create(['role' => 'pelanggan', 'is_active' => true]);
        $this->actingAs($pelanggan)->post(route('admin.photos.update'), [
            'type' => 'hero', 'image' => UploadedFile::fake()->image('a.jpg'),
        ])->assertForbidden();
    }
}