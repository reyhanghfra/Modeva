<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $petugas;
    protected User $pelanggan;

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
            'name' => 'Pelanggan Setia',
            'email' => 'pelanggan@modeva.test',
            'role' => 'pelanggan',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_user_index_with_search_and_filters(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index', [
            'search' => 'Petugas',
            'role' => 'petugas',
            'status' => 'aktif',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Data Pengguna');
        $response->assertSee('Petugas Toko');
        $response->assertDontSee('Pelanggan Setia');
    }

    public function test_user_index_pagination_works(): void
    {
        User::factory()->count(15)->create();

        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Menampilkan');
        $response->assertSee('dari');
    }

    public function test_admin_can_create_user_with_valid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Staf Baru',
            'email' => 'stafbaru@modeva.test',
            'role' => 'petugas',
            'is_active' => '1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'stafbaru@modeva.test',
            'role' => 'petugas',
            'is_active' => 1,
        ]);
    }

    public function test_create_user_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'role' => 'invalid-role',
            'password' => '123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'role', 'password']);
    }

    public function test_create_user_fails_with_duplicate_email(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'name' => 'Duplikat',
            'email' => 'admin@modeva.test',
            'role' => 'petugas',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_can_update_user(): void
    {
        $targetUser = User::factory()->create([
            'name' => 'Nama Lama',
            'email' => 'lama@modeva.test',
            'role' => 'pelanggan',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $targetUser), [
            'name' => 'Nama Baru',
            'email' => 'baru@modeva.test',
            'role' => 'petugas',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'name' => 'Nama Baru',
            'email' => 'baru@modeva.test',
            'role' => 'petugas',
        ]);
    }

    public function test_admin_can_toggle_user_status(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $targetUser));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_active' => false,
        ]);

        $response2 = $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $targetUser));
        $response2->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $targetUser->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_cannot_deactivate_or_demote_themselves(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.users.update', $this->admin), [
            'name' => 'Admin Utama',
            'email' => 'admin@modeva.test',
            'role' => 'pelanggan',
            'is_active' => '0',
        ]);

        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
        $this->assertSame('admin', $this->admin->role);

        // Uji toggle status diri sendiri
        $responseToggle = $this->actingAs($this->admin)->patch(route('admin.users.toggle-status', $this->admin));
        $responseToggle->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertTrue($this->admin->is_active);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_cannot_deactivate_demote_or_delete_last_active_admin(): void
    {
        // $this->admin adalah satu-satunya admin aktif
        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $this->admin));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_user_with_orders_cannot_be_deleted(): void
    {
        // Buat tabel orders sementara untuk simulasi
        Schema::create('orders', function ($table) {
            $table->id();
            $table->string('order_number');
            $table->foreignId('user_id');
            $table->unsignedBigInteger('total');
            $table->string('status')->default('menunggu_pembayaran');
            $table->timestamps();
        });

        $userWithOrder = User::factory()->create();
        \Illuminate\Support\Facades\DB::table('orders')->insert([
            'order_number' => 'MDV-999999',
            'user_id' => $userWithOrder->id,
            'total' => 150000,
            'status' => 'menunggu_pembayaran',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.users.destroy', $userWithOrder));

        $response->assertSessionHas('error', 'Pengguna memiliki riwayat pesanan, nonaktifkan saja.');
        $this->assertDatabaseHas('users', ['id' => $userWithOrder->id]);
    }

    public function test_non_admin_cannot_access_users_management(): void
    {
        $response = $this->actingAs($this->petugas)->get(route('admin.users.index'));
        $response->assertStatus(403);

        $response2 = $this->actingAs($this->pelanggan)->get(route('admin.users.index'));
        $response2->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_users_management(): void
    {
        $response = $this->get(route('admin.users.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $inactive = User::factory()->create([
            'email' => 'user.inactive@modeva.test',
            'password' => 'password',
            'is_active' => false,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'user.inactive@modeva.test',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }
}
