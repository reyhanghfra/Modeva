<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\User;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $pelanggan;
    protected Customer $customerProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@modeva.test',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->petugas = User::factory()->create([
            'name' => 'Petugas Toko',
            'email' => 'petugas@modeva.test',
            'role' => 'petugas',
            'is_active' => true,
        ]);

        $this->pelanggan = User::factory()->create([
            'name' => 'Anindya Putri',
            'email' => 'anindya@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $this->customerProfile = Customer::create([
            'user_id' => $this->pelanggan->id,
            'phone' => '081345678901',
            'address' => 'Jl. Dago Pakar Barat No. 42, Bandung',
        ]);
    }

    public function test_admin_can_view_customers_index_with_required_columns(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.customers.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Pelanggan');
        $response->assertSee('Anindya Putri');
        $response->assertSee('anindya@modeva.test');
        $response->assertSee('081345678901');
        $response->assertSee('Jl. Dago Pakar Barat No. 42, Bandung');
        $response->assertSee($this->pelanggan->created_at->format('d M Y'));

        // Admin dan petugas tidak boleh muncul di daftar pelanggan
        $response->assertDontSee('admin@modeva.test');
        $response->assertDontSee('petugas@modeva.test');
        $response->assertDontSee('Petugas Toko');
    }

    public function test_admin_can_view_customer_detail(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.customers.show', $this->pelanggan));

        $response->assertStatus(200);
        $response->assertSee('Detail Pelanggan');
        $response->assertSee('Anindya Putri');
        $response->assertSee('anindya@modeva.test');
        $response->assertSee('081345678901');
        $response->assertSee('Jl. Dago Pakar Barat No. 42, Bandung');
    }

    public function test_viewing_non_customer_user_via_customer_route_returns_404(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.customers.show', $this->petugas));
        $response->assertStatus(404);

        $responseEdit = $this->actingAs($this->admin)->get(route('admin.customers.edit', $this->admin));
        $responseEdit->assertStatus(404);
    }

    public function test_admin_can_create_customer_with_valid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.customers.store'), [
            'name' => 'Maya Lestari',
            'email' => 'maya.baru@modeva.test',
            'phone' => '081567890123',
            'address' => 'Jl. Darmo Permai Selatan No. 12, Surabaya',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Maya Lestari',
            'email' => 'maya.baru@modeva.test',
            'role' => 'pelanggan',
            'is_active' => 1,
        ]);

        $createdUser = User::where('email', 'maya.baru@modeva.test')->firstOrFail();

        $this->assertDatabaseHas('customers', [
            'user_id' => $createdUser->id,
            'phone' => '081567890123',
            'address' => 'Jl. Darmo Permai Selatan No. 12, Surabaya',
        ]);
    }

    public function test_create_customer_fails_with_invalid_or_duplicate_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.customers.store'), [
            'name' => '',
            'email' => 'anindya@modeva.test',
            'phone' => 'abc',
            'address' => '',
            'password' => '123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'address', 'password']);
    }

    public function test_admin_can_update_customer_and_creates_missing_profile(): void
    {
        $pelangganTanpaProfil = User::factory()->create([
            'name' => 'Rizky Pratama',
            'email' => 'rizky@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.customers.update', $pelangganTanpaProfil), [
            'name' => 'Rizky Pratama Updated',
            'email' => 'rizky.updated@modeva.test',
            'phone' => '081789012345',
            'address' => 'Jl. Malioboro No. 88, Yogyakarta',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $pelangganTanpaProfil->id,
            'name' => 'Rizky Pratama Updated',
            'email' => 'rizky.updated@modeva.test',
            'role' => 'pelanggan',
        ]);

        $this->assertDatabaseHas('customers', [
            'user_id' => $pelangganTanpaProfil->id,
            'phone' => '081789012345',
            'address' => 'Jl. Malioboro No. 88, Yogyakarta',
        ]);
    }

    public function test_admin_can_delete_customer_without_orders(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.customers.destroy', $this->pelanggan));

        $response->assertRedirect(route('admin.customers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $this->pelanggan->id]);
        $this->assertDatabaseMissing('customers', ['user_id' => $this->pelanggan->id]);
    }

    public function test_customer_with_orders_cannot_be_deleted(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function ($table) {
                $table->id();
                $table->string('order_number');
                $table->foreignId('user_id');
                $table->unsignedBigInteger('total');
                $table->string('status')->default('menunggu_pembayaran');
                $table->timestamps();
            });
        }

        DB::table('orders')->insert([
            'order_number' => 'MDV-888888',
            'user_id' => $this->pelanggan->id,
            'total' => 250000,
            'status' => 'menunggu_pembayaran',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.customers.destroy', $this->pelanggan));

        $response->assertSessionHas('error', 'Pelanggan memiliki riwayat pesanan, nonaktifkan akun saja.');
        $this->assertDatabaseHas('users', ['id' => $this->pelanggan->id]);
        $this->assertDatabaseHas('customers', ['user_id' => $this->pelanggan->id]);
    }

    public function test_non_admin_cannot_access_customer_management(): void
    {
        $this->actingAs($this->petugas)->get(route('admin.customers.index'))->assertStatus(403);
        $this->actingAs($this->petugas)->get(route('admin.customers.show', $this->pelanggan))->assertStatus(403);
        $this->actingAs($this->petugas)->get(route('admin.customers.create'))->assertStatus(403);
        $this->actingAs($this->petugas)->post(route('admin.customers.store'), [])->assertStatus(403);

        $this->actingAs($this->pelanggan)->get(route('admin.customers.index'))->assertStatus(403);
        $this->actingAs($this->pelanggan)->get(route('admin.customers.show', $this->pelanggan))->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_customer_management(): void
    {
        $this->get(route('admin.customers.index'))->assertRedirect(route('login'));
        $this->get(route('admin.customers.show', $this->pelanggan))->assertRedirect(route('login'));
        $this->get(route('admin.customers.create'))->assertRedirect(route('login'));
    }

    public function test_customer_seeder_populates_profiles_idempotently(): void
    {
        $this->seed(UserSeeder::class);
        $this->seed(CustomerSeeder::class);
        $this->seed(CustomerSeeder::class);

        $pelangganCount = User::where('role', 'pelanggan')->count();
        $profileCount = Customer::count();

        $this->assertSame($pelangganCount, $profileCount);
        $this->assertDatabaseHas('customers', [
            'phone' => '081234567890',
            'address' => 'Jl. Kemang Raya No. 18, Jakarta Selatan',
        ]);
    }
}
